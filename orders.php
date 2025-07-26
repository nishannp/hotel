<?php 
// orders.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<!-- Flatpickr CSS for Date Range Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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
    --warning-color: #f59e0b; /* In-Progress */
    --info-color: #3b82f6;    /* Pending */
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --font-family: 'Poppins', sans-serif;
}

/* Base & Layout */
.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.content-header h1 { color: var(--text-primary) !important; font-weight: 600 !important; }
.page-container { padding: 2rem; }
.section-header { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem; }

/* Active Orders Section */
#activeOrdersGrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
.active-order-card { background: var(--bg-content); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); padding: 1.5rem; display: flex; flex-direction: column; }
.active-order-card .card-top { display: flex; justify-content: space-between; align-items: flex-start; }
.active-order-card .order-id { font-size: 1.25rem; font-weight: 600; color: var(--primary-color); }
.active-order-card .status-badge { font-size: 0.85rem; font-weight: 500; padding: 4px 10px; border-radius: 99px; color: white; }
.active-order-card .card-main { margin: 1rem 0; font-size: 1rem; color: var(--text-primary); }
.active-order-card .card-footer { margin-top: auto; font-size: 0.9rem; color: var(--text-secondary); }
.status-In-Progress { background-color: var(--warning-color); }
.status-Pending { background-color: var(--info-color); }

/* Filters Bar */
.filters-bar { display: flex; gap: 1rem; align-items: center; padding: 0.75rem; background-color: var(--bg-content); border-radius: 10px; margin-bottom: 1rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); flex-wrap: wrap; }
.search-bar { display: flex; align-items: center; background-color: var(--bg-main); border-radius: 8px; padding: 0 8px; flex-grow: 1; min-width: 200px; }
.search-bar .material-icons-outlined { color: var(--text-secondary); }
.search-bar input { border: none; background: transparent; padding: 10px; width: 100%; font-size: 0.95rem; }
.search-bar input:focus { outline: none; }
.filter-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.filter-btn { padding: 8px 16px; border: 1px solid var(--border-color); background-color: transparent; color: var(--text-secondary); font-weight: 500; border-radius: 8px; cursor: pointer; transition: all 0.2s ease-in-out; }
.filter-btn:hover { background-color: #f3f4f6; color: var(--text-primary); }
.filter-btn.active { color: white; border-color: var(--primary-color); background-color: var(--primary-color); }
#dateRangePicker { background-color: var(--bg-main); border-radius: 8px; padding: 8px; border: 1px solid var(--border-color); color: var(--text-primary); cursor: pointer; min-width: 220px; text-align: center; }

/* Orders List (Accordion) */
#ordersList { display: flex; flex-direction: column; gap: 0.75rem; }
.order-item { background-color: var(--bg-content); border-radius: 8px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: box-shadow 0.2s ease; }
.order-summary { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto; align-items: center; padding: 1rem 1.5rem; cursor: pointer; }
.order-summary > div { display: flex; flex-direction: column; }
.order-summary .label { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.25rem; }
.order-summary .value { font-weight: 500; color: var(--text-primary); }
.order-summary .value.order-id { color: var(--primary-color); }
.status-badge-history { font-size: 0.8rem; font-weight: 500; padding: 3px 8px; border-radius: 99px; color: white; text-align: center; }
.status-Completed { background-color: var(--success-color); }
.status-Cancelled { background-color: var(--danger-color); }
.expand-icon { transition: transform 0.3s ease; }
.order-item.open .expand-icon { transform: rotate(180deg); }

.order-details { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, padding 0.4s ease-out; }
.order-item.open .order-details { max-height: 500px; }
.details-content { padding: 0 1.5rem 1.5rem; border-top: 1px solid var(--border-color); margin-top: 1rem; }
.details-content h4 { font-weight: 600; margin-bottom: 1rem; }
.detail-item { display: flex; align-items: center; gap: 1rem; padding: 0.5rem 0; }
.detail-item:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
.detail-item-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
.detail-item-info { flex-grow: 1; }
.detail-item-name { font-weight: 500; }
.detail-item-qty { color: var(--text-secondary); font-size: 0.9rem; }
.detail-item-subtotal { font-weight: 500; }

/* Pagination */
.pagination-container { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 0; }
.pagination-controls button { background: var(--bg-content); border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 12px; border-radius: 8px; cursor: pointer; margin: 0 2px; transition: all 0.2s ease; }
.pagination-controls button:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
.pagination-controls button.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
.pagination-controls button:disabled { cursor: not-allowed; opacity: 0.5; }
.pagination-info { font-size: 0.9rem; color: var(--text-secondary); }

/* Empty State */
.empty-state { display: none; text-align: center; padding: 4rem 2rem; background-color: var(--bg-content); border: 2px dashed var(--border-color); border-radius: 12px; color: var(--text-secondary); }
.empty-state .material-icons-outlined { font-size: 64px; color: var(--primary-color); opacity: 0.5; }
.empty-state h3 { margin-top: 1rem; font-size: 1.5rem; color: var(--text-primary); }
</style>

<div class="page-container">
    <h2 class="section-header">Active Orders</h2>
    <div id="activeOrdersGrid"></div>
    <div id="noActiveOrders" class="empty-state" style="padding: 2rem; margin-bottom: 2rem;">
        <span class="material-icons-outlined">done_all</span>
        <h3>No Active Orders</h3>
        <p>All pending and in-progress orders will appear here.</p>
    </div>

    <h2 class="section-header" style="margin-top: 2rem;">Order History</h2>
    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search by Order ID, Table, Staff...">
        </div>
        <div id="statusFilters" class="filter-group"></div>
        <input type="text" id="dateRangePicker" placeholder="Filter by date range">
    </div>

    <div id="ordersList"></div>
    <div id="paginationContainer" class="pagination-container"></div>
    
    <div id="emptyStateHistory" class="empty-state">
        <span class="material-icons-outlined">receipt_long</span>
        <h3>No Orders Found</h3>
        <p>Try adjusting your search or filter criteria.</p>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Order Management';

    const App = {
        elements: {
            activeGrid: document.getElementById('activeOrdersGrid'),
            noActiveOrders: document.getElementById('noActiveOrders'),
            historyList: document.getElementById('ordersList'),
            emptyStateHistory: document.getElementById('emptyStateHistory'),
            searchInput: document.getElementById('searchInput'),
            statusFilters: document.getElementById('statusFilters'),
            datePicker: document.getElementById('dateRangePicker'),
            paginationContainer: document.getElementById('paginationContainer'),
        },
        state: {
            activeOrders: [],
            orderHistory: [],
            pagination: {},
            filters: {
                searchTerm: '',
                status: 'All',
                startDate: '',
                endDate: '',
            },
        },
        datePickerInstance: null,

        init() {
            this.initDatePicker();
            this.bindEvents();
            this.loadActiveOrders();
            this.fetchOrderHistory(1);
            this.populateStatusFilters();
        },
        
        initDatePicker() {
            this.datePickerInstance = flatpickr(this.elements.datePicker, {
                mode: "range",
                dateFormat: "Y-m-d",
                onChange: (selectedDates) => {
                    if (selectedDates.length === 2) {
                        this.state.filters.startDate = this.formatDate(selectedDates[0]);
                        this.state.filters.endDate = this.formatDate(selectedDates[1]);
                    } else {
                        this.state.filters.startDate = '';
                        this.state.filters.endDate = '';
                    }
                    this.fetchOrderHistory(1);
                }
            });
        },

        bindEvents() {
            this.elements.searchInput.addEventListener('input', (e) => {
                this.state.filters.searchTerm = e.target.value.toLowerCase();
                this.fetchOrderHistory(1);
            });
            this.elements.statusFilters.addEventListener('click', (e) => {
                const filterBtn = e.target.closest('.filter-btn');
                if (filterBtn) {
                    this.elements.statusFilters.querySelector('.active')?.classList.remove('active');
                    filterBtn.classList.add('active');
                    this.state.filters.status = filterBtn.dataset.status;
                    this.fetchOrderHistory(1);
                }
            });
            this.elements.historyList.addEventListener('click', (e) => {
                const summary = e.target.closest('.order-summary');
                if (summary) summary.parentElement.classList.toggle('open');
            });
            this.elements.paginationContainer.addEventListener('click', (e) => {
                if (e.target.tagName === 'BUTTON' && e.target.dataset.page) {
                    this.fetchOrderHistory(parseInt(e.target.dataset.page));
                }
            });
        },
        
        async loadActiveOrders() {
            try {
                const response = await fetch('ajax/ajax_handler_orders.php?action=fetchActiveOrders');
                const data = await response.json();
                this.state.activeOrders = data.success ? data.data : [];
                this.renderActiveOrders();
            } catch (error) {
                console.error("Error loading active orders:", error);
            }
        },

        async fetchOrderHistory(page) {
            const { searchTerm, status, startDate, endDate } = this.state.filters;
            const url = `ajax/ajax_handler_orders.php?action=fetchOrderHistory&page=${page}&searchTerm=${searchTerm}&status=${status}&startDate=${startDate}&endDate=${endDate}`;
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    this.state.orderHistory = data.data;
                    this.state.pagination = data.pagination;
                    this.renderHistory();
                    this.renderPagination();
                }
            } catch (error) {
                console.error("Error loading order history:", error);
            }
        },

        renderActiveOrders() {
            const grid = this.elements.activeGrid;
            grid.innerHTML = '';
            if (this.state.activeOrders.length === 0) {
                this.elements.noActiveOrders.style.display = 'block';
                grid.style.display = 'none';
            } else {
                this.elements.noActiveOrders.style.display = 'none';
                grid.style.display = 'grid';
                this.state.activeOrders.forEach(order => {
                    grid.insertAdjacentHTML('beforeend', this.createActiveOrderCard(order));
                });
            }
        },

        renderHistory() {
            const list = this.elements.historyList;
            list.innerHTML = '';
            if (this.state.orderHistory.length === 0) {
                this.elements.emptyStateHistory.style.display = 'block';
            } else {
                this.elements.emptyStateHistory.style.display = 'none';
                this.state.orderHistory.forEach(order => {
                    list.insertAdjacentHTML('beforeend', this.createHistoryOrderItem(order));
                });
            }
        },

        renderPagination() {
            const { currentPage, totalPages } = this.state.pagination;
            if (totalPages <= 1) {
                this.elements.paginationContainer.innerHTML = '';
                return;
            }
            let html = `<div class="pagination-info">Page ${currentPage} of ${totalPages}</div>`;
            html += `<div class="pagination-controls">`;
            html += `<button data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>&laquo; Prev</button>`;
            // Simple pagination links for brevity, can be expanded with ellipses
            for (let i = 1; i <= totalPages; i++) {
                html += `<button data-page="${i}" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
            }
            html += `<button data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>Next &raquo;</button>`;
            html += `</div>`;
            this.elements.paginationContainer.innerHTML = html;
        },

        createActiveOrderCard(order) {
            return `
                <div class="active-order-card">
                    <div class="card-top">
                        <div class="order-id">#${order.OrderID}</div>
                        <div class="status-badge status-${order.OrderStatus}">${order.OrderStatus}</div>
                    </div>
                    <div class="card-main">
                        Table: <strong>${order.TableNumber}</strong><br>
                        Staff: <strong>${this.escapeHTML(order.FirstName)}</strong>
                    </div>
                    <div class="card-footer">
                        <span class="material-icons-outlined" style="font-size: 1em; vertical-align: middle;">schedule</span>
                        ${new Date(order.OrderTime).toLocaleTimeString()}
                    </div>
                </div>
            `;
        },

        createHistoryOrderItem(order) {
            const defaultImg = 'https://via.placeholder.com/50/e5e7eb/6b7280?text=?';
            const detailsHtml = order.Details.map(d => `
                <div class="detail-item">
                    <img src="${d.ItemImageUrl || defaultImg}" alt="${this.escapeHTML(d.ItemName)}" class="detail-item-img">
                    <div class="detail-item-info">
                        <div class="detail-item-name">${this.escapeHTML(d.ItemName)}</div>
                        <div class="detail-item-qty">${d.Quantity} x $${(d.Subtotal / d.Quantity).toFixed(2)}</div>
                    </div>
                    <div class="detail-item-subtotal">$${parseFloat(d.Subtotal).toFixed(2)}</div>
                </div>
            `).join('');

            return `
                <div class="order-item">
                    <div class="order-summary">
                        <div><span class="label">Order ID</span><span class="value order-id">#${order.OrderID}</span></div>
                        <div><span class="label">Table / Staff</span><span class="value">${order.TableNumber} / ${this.escapeHTML(order.FirstName)}</span></div>
                        <div><span class="label">Date & Time</span><span class="value">${new Date(order.OrderTime).toLocaleString()}</span></div>
                        <div><span class="label">Status</span><span class="value"><span class="status-badge-history status-${order.OrderStatus}">${order.OrderStatus}</span></span></div>
                        <div><span class="label">Total</span><span class="value">$${parseFloat(order.TotalAmount).toFixed(2)}</span></div>
                        <div class="expand-icon"><span class="material-icons-outlined">expand_more</span></div>
                    </div>
                    <div class="order-details"><div class="details-content"><h4>Order Details</h4>${detailsHtml}</div></div>
                </div>
            `;
        },

        populateStatusFilters() {
            const statuses = ['All', 'Completed', 'Cancelled', 'In-Progress', 'Pending'];
            this.elements.statusFilters.innerHTML = statuses.map(s => `<button class="filter-btn ${s === 'All' ? 'active' : ''}" data-status="${s}">${s}</button>`).join('');
        },

        formatDate(date) {
            const d = new Date(date),
                  month = '' + (d.getMonth() + 1),
                  day = '' + d.getDate(),
                  year = d.getFullYear();
            return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[match]));
        }
    };
    
    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>