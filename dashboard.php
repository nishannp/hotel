<?php 
// dashboard.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<!-- Chart.js for data visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<style>
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #4f46e5;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --font-family: 'Poppins', sans-serif;
}

/* Base & Layout */
.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.content-header h1 { color: var(--text-primary) !important; font-weight: 600 !important; }
.page-container { padding: 2rem; }
.dashboard-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; }

/* Tabs */
.dashboard-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); }
.tab-link { padding: 0.75rem 1.5rem; border: none; background: transparent; cursor: pointer; font-size: 1rem; font-weight: 500; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all 0.2s ease; }
.tab-link:hover { color: var(--text-primary); }
.tab-link.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* General Card Style */
.db-card { background-color: var(--bg-content); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
.db-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.db-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }

/* KPI Card */
.kpi-card { display: flex; flex-direction: column; }
.kpi-value { font-size: 2rem; font-weight: 700; color: var(--text-primary); }
.kpi-label { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem; }
.kpi-comparison { display: flex; align-items: center; font-size: 0.9rem; font-weight: 500; }
.kpi-comparison .material-icons-outlined { font-size: 1.25rem; margin-right: 0.25rem; }
.kpi-comparison.positive { color: var(--success-color); }
.kpi-comparison.negative { color: var(--danger-color); }

/* Chart Card */
.chart-container { height: 350px; }

/* List Card */
.list-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; }
.list-item:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
.list-item .item-info { flex-grow: 1; }
.list-item .item-name { font-weight: 500; color: var(--text-primary); }
.list-item .item-subtext { font-size: 0.85rem; color: var(--text-secondary); }
.list-item .item-trailing { font-weight: 500; text-align: right; }
.status-badge { font-size: 0.8rem; font-weight: 500; padding: 3px 8px; border-radius: 99px; color: white; }
.status-In-Progress { background-color: var(--warning-color); }
.status-Pending { background-color: var(--info-color); }

/* Responsive Grid */
@media (min-width: 768px) {
    .kpi-card-grid { grid-column: span 12; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .chart-card { grid-column: span 8; }
    .pie-chart-card { grid-column: span 4; }
    .list-card { grid-column: span 6; }
}
</style>

<div class="page-container">
    <div class="dashboard-tabs">
        <button class="tab-link active" data-tab="overview">Overview</button>
        <button class="tab-link" data-tab="sales">Sales Analytics</button>
        <button class="tab-link" data-tab="menu">Menu & Inventory</button>
    </div>

    <!-- Tab 1: Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="dashboard-grid">
            <div class="kpi-card-grid">
                <div class="db-card kpi-card">
                    <div class="kpi-label">Today's Revenue</div>
                    <div id="kpi-revenue" class="kpi-value">$0.00</div>
                    <div id="kpi-revenue-comp" class="kpi-comparison"></div>
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label">Today's Orders</div>
                    <div id="kpi-orders" class="kpi-value">0</div>
                    <div class="kpi-comparison"></div> <!-- Placeholder for potential future use -->
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label">Active Tables</div>
                    <div id="kpi-tables" class="kpi-value">0</div>
                    <div class="kpi-comparison"></div> <!-- Placeholder -->
                </div>
            </div>
            <div class="db-card pie-chart-card">
                <div class="db-card-header"><h3 class="db-card-title">Table Status</h3></div>
                <div class="chart-container" style="height: 250px;"><canvas id="tableStatusChart"></canvas></div>
            </div>
            <div class="db-card" style="grid-column: span 8;">
                <div class="db-card-header"><h3 class="db-card-title">Live Orders</h3></div>
                <div id="active-orders-list"></div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Sales Analytics -->
    <div id="tab-sales" class="tab-content">
        <div class="dashboard-grid">
            <div class="db-card chart-card" style="grid-column: span 12;">
                <div class="db-card-header"><h3 class="db-card-title">Revenue (Last 30 Days)</h3></div>
                <div class="chart-container"><canvas id="salesChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Menu & Inventory -->
    <div id="tab-menu" class="tab-content">
        <div class="dashboard-grid">
            <div class="db-card list-card">
                <div class="db-card-header"><h3 class="db-card-title">Best Sellers (This Month)</h3></div>
                <div id="best-sellers-list"></div>
            </div>
            <div class="db-card list-card">
                <div class="db-card-header"><h3 class="db-card-title">Worst Sellers (This Month)</h3></div>
                <div id="worst-sellers-list"></div>
            </div>
            <div class="db-card list-card" style="grid-column: span 12;">
                 <div class="db-card-header"><h3 class="db-card-title">Low Stock Items</h3></div>
                <div id="low-stock-list"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Dashboard';
    
    const App = {
        charts: {},
        elements: {
            tabs: document.querySelectorAll('.tab-link'),
            tabContents: document.querySelectorAll('.tab-content'),
            // KPI
            kpiRevenue: document.getElementById('kpi-revenue'),
            kpiRevenueComp: document.getElementById('kpi-revenue-comp'),
            kpiOrders: document.getElementById('kpi-orders'),
            kpiTables: document.getElementById('kpi-tables'),
            // Lists
            activeOrdersList: document.getElementById('active-orders-list'),
            bestSellersList: document.getElementById('best-sellers-list'),
            worstSellersList: document.getElementById('worst-sellers-list'),
            lowStockList: document.getElementById('low-stock-list'),
            // Canvases
            salesChartCanvas: document.getElementById('salesChart'),
            tableStatusChartCanvas: document.getElementById('tableStatusChart'),
        },

        init() {
            this.bindEvents();
            this.fetchData();
            // setInterval(() => this.fetchData(), 60000); // Optional: Refresh every 60 seconds
        },

        bindEvents() {
            this.elements.tabs.forEach(tab => {
                tab.addEventListener('click', () => this.activateTab(tab));
            });
        },

        activateTab(clickedTab) {
            this.elements.tabs.forEach(tab => tab.classList.remove('active'));
            clickedTab.classList.add('active');
            const tabName = clickedTab.dataset.tab;
            this.elements.tabContents.forEach(content => {
                content.classList.toggle('active', content.id === `tab-${tabName}`);
            });
        },

        async fetchData() {
            try {
                const response = await fetch('ajax/ajax_handler_dashboard.php');
                const result = await response.json();
                if (result.success) {
                    this.render(result.data);
                } else { throw new Error(result.message || 'Failed to fetch data'); }
            } catch (error) {
                console.error("Error fetching dashboard data:", error);
                // You could display an error message on the UI here
            }
        },

        render(data) {
            this.renderKPIs(data.kpi);
            this.renderTableStatusChart(data.table_statuses);
            this.renderSalesChart(data.sales_chart);
            this.renderList(this.elements.activeOrdersList, data.active_orders, this.createActiveOrderHTML);
            this.renderList(this.elements.bestSellersList, data.menu_insights.best_sellers, this.createMenuItemHTML);
            this.renderList(this.elements.worstSellersList, data.menu_insights.worst_sellers, this.createMenuItemHTML);
            this.renderList(this.elements.lowStockList, data.inventory_insights.low_stock, this.createLowStockHTML);
        },

        renderKPIs(kpi) {
            this.elements.kpiRevenue.textContent = `$${parseFloat(kpi.revenue_today || 0).toFixed(2)}`;
            this.elements.kpiOrders.textContent = kpi.orders_today || 0;
            
            const diff = kpi.revenue_today - kpi.revenue_yesterday;
            const percent_diff = kpi.revenue_yesterday > 0 ? (diff / kpi.revenue_yesterday) * 100 : 100;
            const compEl = this.elements.kpiRevenueComp;
            
            if (diff >= 0) {
                compEl.className = 'kpi-comparison positive';
                compEl.innerHTML = `<span class="material-icons-outlined">trending_up</span> +${percent_diff.toFixed(1)}% vs yesterday`;
            } else {
                compEl.className = 'kpi-comparison negative';
                compEl.innerHTML = `<span class="material-icons-outlined">trending_down</span> ${percent_diff.toFixed(1)}% vs yesterday`;
            }
        },

        renderChart(canvas, type, data, options) {
            const id = canvas.id;
            if (this.charts[id]) this.charts[id].destroy();
            this.charts[id] = new Chart(canvas.getContext('2d'), { type, data, options });
        },

        renderTableStatusChart(statuses) {
            this.elements.kpiTables.textContent = statuses.Occupied || 0;
            const data = {
                labels: ['Available', 'Occupied', 'Reserved'],
                datasets: [{
                    data: [statuses.Available, statuses.Occupied, statuses.Reserved],
                    backgroundColor: ['var(--success-color)', 'var(--danger-color)', 'var(--warning-color)'],
                    borderWidth: 0,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
            };
            this.renderChart(this.elements.tableStatusChartCanvas, 'pie', data, options);
        },

        renderSalesChart(salesData) {
            const data = {
                labels: salesData.labels,
                datasets: [{
                    label: 'Revenue',
                    data: salesData.values,
                    borderColor: 'var(--primary-color)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: value => '$' + value } } },
                plugins: { legend: { display: false } }
            };
            this.renderChart(this.elements.salesChartCanvas, 'line', data, options);
        },

        renderList(container, items, htmlFactory) {
            if (!items || items.length === 0) {
                container.innerHTML = `<p style="color: var(--text-secondary); text-align: center; padding: 1rem 0;">No data to display.</p>`;
                return;
            }
            container.innerHTML = items.map(htmlFactory).join('');
        },

        createActiveOrderHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-name">Order #${item.OrderID} (Table ${item.TableNumber})</div>
                        <div class="item-subtext">$${parseFloat(item.TotalAmount).toFixed(2)}</div>
                    </div>
                    <div class="item-trailing">
                        <span class="status-badge status-${item.OrderStatus}">${item.OrderStatus}</span>
                    </div>
                </div>`;
        },

        createMenuItemHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info"><div class="item-name">${item.Name}</div></div>
                    <div class="item-trailing">${item.order_count} orders</div>
                </div>`;
        },

        createLowStockHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info"><div class="item-name">${item.Name}</div></div>
                    <div class="item-trailing" style="color: var(--danger-color);">
                        ${parseFloat(item.QuantityInStock).toFixed(2)} ${item.UnitOfMeasure}
                    </div>
                </div>`;
        }
    };

    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>