<?php 
require_once 'includes/header.php'; 
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<style>
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #f59e0b; /* Amber for alerts */
    --primary-hover: #d97706;
    --success-color: #10b981;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --font-family: 'Poppins', sans-serif;
}

.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.page-container { padding: 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.75rem; font-weight: 600; }
.page-header .last-updated { font-size: 0.9rem; color: var(--text-secondary); }

.btn {
    border: none; padding: 8px 16px; border-radius: 8px;
    cursor: pointer; font-weight: 500; font-size: 0.9rem;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.2s ease;
}
.btn-success { background-color: var(--success-color); color: white; }
.btn-success:hover { background-color: #059669; }

.table-container { background-color: var(--bg-content); border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid var(--border-color); }
th { background-color: #f9fafb; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; }
tr:last-child td { border-bottom: none; }

.status-badge { padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; font-weight: 600; }
.status-pending { background-color: #fef3c7; color: #92400e; }
.status-acknowledged { background-color: #dbeafe; color: #1d4ed8; }
.status-ordered { background-color: #d1fae5; color: #065f46; }

.stock-level { font-weight: 700; color: #ef4444; }

.empty-state {
    text-align: center; padding: 4rem; background-color: var(--bg-content);
    border: 2px dashed var(--border-color); border-radius: 12px; color: var(--text-secondary);
}
.empty-state .material-icons-outlined { font-size: 64px; color: var(--success-color); }
.empty-state h3 { margin-top: 1rem; font-size: 1.5rem; color: var(--text-primary); }
</style>

<div class="page-container">
    <div class="page-header">
        <h2>Low Stock Alerts</h2>
        <div id="lastUpdated" class="last-updated"></div>
    </div>

    <div class="table-container">
        <table id="alertsTable">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Alert Time</th>
                    <th>Qty at Alert</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="alertsTableBody"></tbody>
        </table>
        <div id="emptyState" style="display: none;">
            <span class="material-icons-outlined">check_circle</span>
            <h3>All Good!</h3>
            <p>No pending low stock alerts.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Low Stock Alerts';

    const App = {
        elements: {
            tableBody: document.getElementById('alertsTableBody'),
            emptyState: document.getElementById('emptyState'),
            lastUpdated: document.getElementById('lastUpdated'),
        },
        
        init() {
            this.loadAlerts();
            setInterval(() => this.loadAlerts(), 15000); // Refresh every 15 seconds
        },

        bindEvents() {
            this.elements.tableBody.addEventListener('click', e => {
                const acknowledgeBtn = e.target.closest('.acknowledge-btn');
                if (acknowledgeBtn) {
                    this.acknowledgeAlert(acknowledgeBtn.dataset.id);
                }
            });
        },

        async loadAlerts() {
            try {
                const response = await fetch('ajax/ajax_handler_low_stock.php?action=fetchAll');
                const data = await response.json();
                if (!data.success) throw new Error(data.message);
                
                this.renderAlerts(data.data);
                this.updateTimestamp();
                this.bindEvents(); // Re-bind events after re-rendering
            } catch (error) {
                showToast('Error loading alerts: ' + error.message, 'error');
            }
        },

        renderAlerts(alerts) {
            this.elements.tableBody.innerHTML = '';
            const pendingAlerts = alerts.filter(a => a.Status === 'Pending');

            if (pendingAlerts.length === 0) {
                this.elements.emptyState.style.display = 'block';
                this.elements.tableBody.style.display = 'none';
            } else {
                this.elements.emptyState.style.display = 'none';
                this.elements.tableBody.style.display = '';
            }

            alerts.forEach(alert => {
                const row = document.createElement('tr');
                const alertTime = new Date(alert.AlertTime).toLocaleString();
                const statusBadge = `<span class="status-badge status-${alert.Status.toLowerCase()}">${alert.Status}</span>`;
                const actionButton = alert.Status === 'Pending' 
                    ? `<button class="btn btn-success acknowledge-btn" data-id="${alert.AlertID}"><span class="material-icons-outlined">check</span> Acknowledge</button>`
                    : '';

                row.innerHTML = `
                    <td>${this.escapeHTML(alert.IngredientName)}</td>
                    <td>${alertTime}</td>
                    <td><span class="stock-level">${parseFloat(alert.QuantityAtAlert).toFixed(2)}</span></td>
                    <td>${parseFloat(alert.ReorderLevelAtAlert).toFixed(2)}</td>
                    <td>${statusBadge}</td>
                    <td>${actionButton}</td>
                `;
                this.elements.tableBody.appendChild(row);
            });
        },

        async acknowledgeAlert(alertId) {
            if (!confirm('Are you sure you want to acknowledge this alert?')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'acknowledge');
                formData.append('alert_id', alertId);

                const response = await fetch('ajax/ajax_handler_low_stock.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (!data.success) throw new Error(data.message);
                
                showToast('Alert acknowledged!', 'success');
                this.loadAlerts();
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            }
        },

        updateTimestamp() {
            this.elements.lastUpdated.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
        },

        escapeHTML(str) {
            return str ? str.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]) : '';
        }
    };

    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>