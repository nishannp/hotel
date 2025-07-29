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
    --info-color: #3b82f6;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
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
.tab-content { display: none; animation: fadeIn 0.5s; }
.tab-content.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* General Card Style */
.db-card { background-color: var(--bg-content); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
.db-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); }
.db-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }

/* KPI Card */
.kpi-card { display: flex; flex-direction: column; }
.kpi-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem; }
.kpi-label .material-icons-outlined { font-size: 1.2rem; }
.kpi-value { font-size: 2rem; font-weight: 700; color: var(--text-primary); }
.kpi-comparison { display: flex; align-items: center; font-size: 0.9rem; font-weight: 500; margin-top: 0.5rem; color: var(--text-secondary); }
.kpi-comparison .material-icons-outlined { font-size: 1.25rem; margin-right: 0.25rem; }
.kpi-comparison.positive { color: var(--success-color); }
.kpi-comparison.negative { color: var(--danger-color); }

/* Chart Card */
.chart-container { height: 350px; }

/* List & Table Styles */
.list-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; }
.list-item:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
.list-item .item-info { flex-grow: 1; }
.list-item .item-name { font-weight: 500; color: var(--text-primary); }
.list-item .item-subtext { font-size: 0.85rem; color: var(--text-secondary); }
.list-item .item-trailing { font-weight: 500; text-align: right; }

.report-table { width: 100%; border-collapse: collapse; }
.report-table th, .report-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.report-table th { font-weight: 600; font-size: 0.9rem; color: var(--text-secondary); background-color: #f9fafb; }
.report-table td { color: var(--text-primary); }
.report-table tbody tr:hover { background-color: #f7f8fc; }
.report-table tbody tr.clickable { cursor: pointer; }
.report-table .progress-bar { background-color: #e5e7eb; border-radius: 6px; height: 8px; width: 100px; overflow: hidden; }
.report-table .progress-bar div { background-color: var(--primary-color); height: 100%; }

.status-badge { font-size: 0.8rem; font-weight: 500; padding: 3px 8px; border-radius: 99px; color: white !important; text-transform: capitalize; }
.status-Completed { background-color: var(--success-color); }
.status-In-Progress { background-color: var(--info-color); }
.status-Pending { background-color: var(--warning-color); }
.status-Cancelled { background-color: var(--danger-color); }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 12px; padding: 2rem; width: 90%; max-width: 600px; box-shadow: var(--shadow-md); animation: fadeIn 0.3s; }
.modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem; }
.modal-title { font-size: 1.2rem; font-weight: 600; }
.modal-close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; }
.modal-body .detail-item { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.modal-body .detail-item:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
.modal-body .item-name { font-weight: 500; }
.modal-body .item-qty { color: var(--text-secondary); }
.modal-body .item-subtotal { font-weight: 500; }

/* Responsive Grid */
@media (min-width: 768px) {
    .kpi-card-grid { grid-column: span 12; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .chart-card { grid-column: span 12; }
    .pie-chart-card { grid-column: span 4; }
    .list-card { grid-column: span 4; }
}
</style>

<div class="page-container">
    <div class="dashboard-tabs">
        <button class="tab-link active" data-tab="overview">Real-Time Overview</button>
        <button class="tab-link" data-tab="report">Today's Detailed Report</button>
        <button class="tab-link" data-tab="performance">Performance Analytics</button>
        <button class="tab-link" data-tab="menu">Menu & Operations</button>
    </div>

    <!-- Tab 1: Real-Time Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="dashboard-grid">
            <div class="kpi-card-grid">
                <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">monetization_on</span> Total Revenue</div>
                    <div id="kpi-total-revenue" class="kpi-value">$0.00</div>
                    <div id="kpi-total-revenue-comp" class="kpi-comparison"></div>
                </div>
                 <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">restaurant</span> Hotel Revenue</div>
                    <div id="kpi-hotel-revenue" class="kpi-value">$0.00</div>
                    <div class="kpi-comparison">Today</div>
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">storefront</span> Store Revenue</div>
                    <div id="kpi-store-revenue" class="kpi-value">$0.00</div>
                    <div class="kpi-comparison">Today</div>
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">receipt_long</span> Hotel Orders</div>
                    <div id="kpi-orders" class="kpi-value">0</div>
                    <div class="kpi-comparison">Completed Today</div>
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">person_add</span> New Customers</div>
                    <div id="kpi-new-customers" class="kpi-value">0</div>
                    <div class="kpi-comparison">Today</div>
                </div>
                <div class="db-card kpi-card">
                    <div class="kpi-label"><span class="material-icons-outlined">notifications_active</span> Pending Alerts</div>
                    <div id="kpi-alerts" class="kpi-value">0</div>
                    <div class="kpi-comparison">Low Stock</div>
                </div>
            </div>
            <div class="db-card" style="grid-column: span 8;">
                <div class="db-card-header"><h3 class="db-card-title">Live Orders Feed</h3></div>
                <div id="active-orders-list" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
            <div class="db-card pie-chart-card">
                <div class="db-card-header"><h3 class="db-card-title">Table Status</h3></div>
                <div class="chart-container" style="height: 250px;"><canvas id="tableStatusChart"></canvas></div>
                <div id="kpi-tables-summary" style="text-align:center; margin-top:1rem; font-weight: 500;"></div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Today's Detailed Report -->
    <div id="tab-report" class="tab-content">
        <div class="dashboard-grid">
            <div class="db-card" style="grid-column: span 12;">
                <div class="db-card-header"><h3 class="db-card-title">Today's Orders (Click to view details)</h3></div>
                <div style="overflow-x: auto;">
                    <table class="report-table" id="report-orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Time</th>
                                <th>Table</th>
                                <th>Customer</th>
                                <th>Staff</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="db-card" style="grid-column: span 5;">
                <div class="db-card-header"><h3 class="db-card-title">Revenue by Payment Method</h3></div>
                <div class="chart-container" style="height: 300px;"><canvas id="paymentMethodsChart"></canvas></div>
            </div>
            <div class="db-card" style="grid-column: span 7;">
                <div class="db-card-header"><h3 class="db-card-title">Today's Staff Performance</h3></div>
                <div style="overflow-x: auto;">
                    <table class="report-table" id="report-staff-table">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Orders Handled</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="db-card" style="grid-column: span 6;">
                <div class="db-card-header"><h3 class="db-card-title">Today's Sales by Menu Item</h3></div>
                 <div style="overflow-x: auto;">
                    <table class="report-table" id="report-menu-sales-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Qty Sold</th>
                                <th>Revenue</th>
                                <th>% of Hotel Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="db-card" style="grid-column: span 6;">
                <div class="db-card-header"><h3 class="db-card-title">Today's Store Sales</h3></div>
                 <div style="overflow-x: auto; max-height: 400px;">
                    <table class="report-table" id="report-store-sales-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Performance Analytics -->
    <div id="tab-performance" class="tab-content">
        <div class="dashboard-grid">
            <div class="db-card chart-card">
                <div class="db-card-header"><h3 class="db-card-title">Revenue (Last 30 Days)</h3></div>
                <div class="chart-container"><canvas id="salesChart"></canvas></div>
            </div>
            <div class="db-card" style="grid-column: span 6;">
                <div class="db-card-header"><h3 class="db-card-title">Sales by Category (This Month)</h3></div>
                <div class="chart-container" style="height: 300px;"><canvas id="categorySalesChart"></canvas></div>
            </div>
            <div class="db-card" style="grid-column: span 6;">
                <div class="db-card-header"><h3 class="db-card-title">Peak Order Hours (This Month)</h3></div>
                <div class="chart-container" style="height: 300px;"><canvas id="peakHoursChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Menu & Operations -->
    <div id="tab-menu" class="tab-content">
        <div class="dashboard-grid">
            <div class="db-card list-card">
                <div class="db-card-header"><h3 class="db-card-title">Best Sellers (Month)</h3></div>
                <div id="best-sellers-list"></div>
            </div>
            <div class="db-card list-card">
                <div class="db-card-header"><h3 class="db-card-title">Worst Sellers (Month)</h3></div>
                <div id="worst-sellers-list"></div>
            </div>
             <div class="db-card list-card">
                <div class="db-card-header"><h3 class="db-card-title">Top Staff (Month)</h3></div>
                <div id="top-staff-list"></div>
            </div>
            <div class="db-card" style="grid-column: span 12;">
                 <div class="db-card-header"><h3 class="db-card-title">Critical Low Stock Items</h3></div>
                <div id="low-stock-list"></div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="order-details-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title" class="modal-title">Order Details</h3>
            <button id="modal-close-btn" class="modal-close">&times;</button>
        </div>
        <div id="modal-body" class="modal-body"></div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Enhanced Dashboard';
    
    const App = {
        charts: {},
        data: {},
        elements: {
            tabs: document.querySelectorAll('.tab-link'),
            tabContents: document.querySelectorAll('.tab-content'),
            // KPI
            kpiTotalRevenue: document.getElementById('kpi-total-revenue'),
            kpiTotalRevenueComp: document.getElementById('kpi-total-revenue-comp'),
            kpiStoreRevenue: document.getElementById('kpi-store-revenue'),
            kpiHotelRevenue: document.getElementById('kpi-hotel-revenue'),
            kpiOrders: document.getElementById('kpi-orders'),
            kpiNewCustomers: document.getElementById('kpi-new-customers'),
            kpiAlerts: document.getElementById('kpi-alerts'),
            kpiTablesSummary: document.getElementById('kpi-tables-summary'),
            // Lists
            activeOrdersList: document.getElementById('active-orders-list'),
            bestSellersList: document.getElementById('best-sellers-list'),
            worstSellersList: document.getElementById('worst-sellers-list'),
            lowStockList: document.getElementById('low-stock-list'),
            topStaffList: document.getElementById('top-staff-list'),
            // Canvases
            salesChartCanvas: document.getElementById('salesChart'),
            tableStatusChartCanvas: document.getElementById('tableStatusChart'),
            categorySalesChartCanvas: document.getElementById('categorySalesChart'),
            peakHoursChartCanvas: document.getElementById('peakHoursChart'),
            paymentMethodsChartCanvas: document.getElementById('paymentMethodsChart'),
            // Report Tables
            reportOrdersTableBody: document.querySelector('#report-orders-table tbody'),
            reportMenuSalesTableBody: document.querySelector('#report-menu-sales-table tbody'),
            reportStaffTableBody: document.querySelector('#report-staff-table tbody'),
            reportStoreSalesTableBody: document.querySelector('#report-store-sales-table tbody'),
            // Modal
            modal: document.getElementById('order-details-modal'),
            modalTitle: document.getElementById('modal-title'),
            modalBody: document.getElementById('modal-body'),
            modalCloseBtn: document.getElementById('modal-close-btn'),
        },

        init() {
            this.bindEvents();
            this.fetchData();
            setInterval(() => this.fetchData(), 30000); // Refresh every 30 seconds
        },

        bindEvents() {
            this.elements.tabs.forEach(tab => {
                tab.addEventListener('click', () => this.activateTab(tab));
            });
            this.elements.reportOrdersTableBody.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                if (row && row.dataset.orderId) {
                    this.showOrderDetailsModal(row.dataset.orderId);
                }
            });
            this.elements.modalCloseBtn.addEventListener('click', () => this.hideModal());
            this.elements.modal.addEventListener('click', (e) => {
                if (e.target === this.elements.modal) this.hideModal();
            });
        },

        activateTab(clickedTab) {
            this.elements.tabs.forEach(tab => tab.classList.remove('active'));
            clickedTab.classList.add('active');
            const tabName = clickedTab.dataset.tab;
            this.elements.tabContents.forEach(content => {
                content.classList.toggle('active', content.id === `tab-${tabName}`);
            });
            setTimeout(() => {
                Object.values(this.charts).forEach(chart => chart.resize());
            }, 10);
        },

        async fetchData() {
            try {
                const response = await fetch('ajax/ajax_handler_dashboard.php');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();
                if (result.success) {
                    this.data = result.data; // Store all data
                    this.render();
                } else { throw new Error(result.message || 'Failed to fetch data from API'); }
            } catch (error) {
                console.error("Error fetching dashboard data:", error);
                document.querySelector('.page-container').innerHTML = `<div class="db-card" style="color: var(--danger-color); text-align: center; padding: 2rem;">Failed to load dashboard data. Please check the server connection or try again later.</div>`;
            }
        },

        render() {
            this.renderKPIs(this.data.kpi, this.data.table_statuses);
            this.renderTableStatusChart(this.data.table_statuses);
            this.renderSalesChart(this.data.sales_chart);
            this.renderList(this.elements.activeOrdersList, this.data.active_orders, this.createActiveOrderHTML);
            this.renderList(this.elements.bestSellersList, this.data.menu_insights.best_sellers, this.createMenuItemHTML);
            this.renderList(this.elements.worstSellersList, this.data.menu_insights.worst_sellers, this.createMenuItemHTML);
            this.renderList(this.elements.lowStockList, this.data.inventory_insights.low_stock, this.createLowStockHTML);
            
            if (this.data.analytics) {
                this.renderCategorySalesChart(this.data.analytics.category_sales);
                this.renderPeakHoursChart(this.data.analytics.peak_hours);
                this.renderList(this.elements.topStaffList, this.data.analytics.top_staff, this.createTopStaffHTML);
            }

            if(this.data.todays_report) {
                this.renderTodaysReport(this.data.todays_report, this.data.kpi);
            }
        },

        renderKPIs(kpi, tables) {
            this.elements.kpiTotalRevenue.textContent = `Rs ${parseFloat(kpi.total_revenue_today || 0).toFixed(2)}`;
            this.elements.kpiStoreRevenue.textContent = `Rs ${parseFloat(kpi.store_revenue_today || 0).toFixed(2)}`;
            this.elements.kpiHotelRevenue.textContent = `Rs ${parseFloat(kpi.hotel_revenue_today || 0).toFixed(2)}`;
            this.elements.kpiOrders.textContent = kpi.completed_orders_today || 0;
            this.elements.kpiNewCustomers.textContent = kpi.new_customers_today || 0;
            this.elements.kpiAlerts.textContent = kpi.low_stock_alerts || 0;
            
            const diff = kpi.total_revenue_today - kpi.total_revenue_yesterday;
            const percent_diff = kpi.total_revenue_yesterday > 0 ? (diff / kpi.total_revenue_yesterday) * 100 : (kpi.total_revenue_today > 0 ? 100 : 0);
            const compEl = this.elements.kpiTotalRevenueComp;
            
            if (diff >= 0) {
                compEl.className = 'kpi-comparison positive';
                compEl.innerHTML = `<span class="material-icons-outlined">trending_up</span> +${percent_diff.toFixed(1)}% vs yesterday`;
            } else {
                compEl.className = 'kpi-comparison negative';
                compEl.innerHTML = `<span class="material-icons-outlined">trending_down</span> ${percent_diff.toFixed(1)}% vs yesterday`;
            }
            
            const totalTables = Object.values(tables).reduce((a, b) => a + b, 0);
            this.elements.kpiTablesSummary.innerHTML = `<strong>${tables.Occupied || 0}</strong> Occupied / <strong>${totalTables}</strong> Total`;
        },

        renderChart(canvas, type, data, options) {
            if (!canvas) return;
            const id = canvas.id;
            if (this.charts[id]) this.charts[id].destroy();
            this.charts[id] = new Chart(canvas.getContext('2d'), { type, data, options });
        },

        renderTableStatusChart(statuses) {
            const data = {
                labels: ['Available', 'Occupied', 'Reserved'],
                datasets: [{
                    data: [statuses.Available, statuses.Occupied, statuses.Reserved],
                    backgroundColor: ['var(--success-color)', 'var(--danger-color)', 'var(--warning-color)'],
                    borderColor: 'var(--bg-content)',
                    borderWidth: 3,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false, cutout: '50%',
                plugins: { 
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20 } }, 
                    tooltip: { callbacks: { label: (c) => ` ${c.label}: ${c.raw} tables` } } 
                }
            };
            this.renderChart(this.elements.tableStatusChartCanvas, 'doughnut', data, options);
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
                    tension: 0.4,
                    pointRadius: 2,
                    pointBackgroundColor: 'var(--primary-color)',
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                scales: { 
                    y: { beginAtZero: true, ticks: { callback: value => 'Rs ' + value } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            };
            this.renderChart(this.elements.salesChartCanvas, 'line', data, options);
        },
        
        renderCategorySalesChart(categoryData) {
            const labels = categoryData.map(c => c.CategoryName);
            const values = categoryData.map(c => c.category_total);
            const data = {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899'],
                    borderColor: 'var(--bg-content)',
                    borderWidth: 2,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } }
            };
            this.renderChart(this.elements.categorySalesChartCanvas, 'pie', data, options);
        },

        renderPeakHoursChart(peakHoursData) {
            const labels = Array.from({length: 24}, (_, i) => i.toString().padStart(2, '0'));
            const data = {
                labels: labels,
                datasets: [{
                    label: 'Number of Orders',
                    data: peakHoursData,
                    backgroundColor: 'rgba(79, 70, 229, 0.6)',
                    borderColor: 'var(--primary-color)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            };
            this.renderChart(this.elements.peakHoursChartCanvas, 'bar', data, options);
        },

        renderList(container, items, htmlFactory) {
            if (!container) return;
            if (!items || items.length === 0) {
                container.innerHTML = `<div class="list-item" style="justify-content: center; color: var(--text-secondary); padding: 1rem 0;">No data available.</div>`;
                return;
            }
            container.innerHTML = items.map(htmlFactory.bind(this)).join('');
        },

        renderTable(tbody, items, rowFactory) {
            if (!tbody) return;
            if (!items || items.length === 0) {
                const colSpan = tbody.parentElement.querySelector('thead th').length;
                tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 1rem;">No data available for today.</td></tr>`;
                return;
            }
            tbody.innerHTML = items.map(rowFactory.bind(this)).join('');
        },

        renderTodaysReport(reportData, kpiData) {
            this.renderTable(this.elements.reportOrdersTableBody, reportData.orders, this.createReportOrderRowHTML);
            this.renderTable(this.elements.reportStaffTableBody, reportData.staff_performance, this.createReportStaffRowHTML);
            this.renderPaymentMethodsChart(reportData.payment_methods);
            
            const totalHotelRevenue = kpiData.hotel_revenue_today;
            this.renderTable(this.elements.reportMenuSalesTableBody, reportData.menu_sales, (item) => this.createReportMenuSaleRowHTML(item, totalHotelRevenue));
            this.renderTable(this.elements.reportStoreSalesTableBody, reportData.store_sales, this.createReportStoreSaleRowHTML);
        },

        renderPaymentMethodsChart(paymentData) {
            const labels = paymentData.map(p => p.PaymentMethod);
            const values = paymentData.map(p => p.TotalAmount);
            const data = {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderColor: 'var(--bg-content)',
                    borderWidth: 2,
                }]
            };
            const options = {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: { callbacks: { label: (c) => ` ${c.label}: Rs ${parseFloat(c.raw).toFixed(2)}` } }
                }
            };
            this.renderChart(this.elements.paymentMethodsChartCanvas, 'doughnut', data, options);
        },

        createActiveOrderHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-name">Order #${item.OrderID} (Table ${item.TableNumber})</div>
                        <div class="item-subtext">Rs ${parseFloat(item.TotalAmount).toFixed(2)}</div>
                    </div>
                    <div class="item-trailing">
                        <span class="status-badge status-${item.OrderStatus.replace(' ', '-')}">${item.OrderStatus}</span>
                    </div>
                </div>`;
        },

        createMenuItemHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info"><div class="item-name">${item.Name}</div></div>
                    <div class="item-trailing"><strong>${item.order_count}</strong> orders</div>
                </div>`;
        },

        createLowStockHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info"><div class="item-name">${item.Name}</div></div>
                    <div class="item-trailing" style="color: var(--danger-color);">
                        <strong>${parseFloat(item.QuantityInStock).toFixed(2)}</strong> ${item.UnitOfMeasure} left
                    </div>
                </div>`;
        },
        
        createTopStaffHTML(item) {
            return `
                <div class="list-item">
                    <div class="item-info"><div class="item-name">${item.FirstName} ${item.LastName}</div></div>
                    <div class="item-trailing"><strong>${item.order_count}</strong> orders</div>
                </div>`;
        },

        createReportOrderRowHTML(item) {
            const orderTime = new Date(item.OrderTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const customerName = (item.CustomerFirstName) ? `${item.CustomerFirstName} ${item.CustomerLastName}` : 'Walk-in';
            return `
                <tr class="clickable" data-order-id="${item.OrderID}">
                    <td>#${item.OrderID}</td>
                    <td>${orderTime}</td>
                    <td>${item.TableNumber}</td>
                    <td>${customerName}</td>
                    <td>${item.StaffFirstName} ${item.StaffLastName}</td>
                    <td>Rs ${parseFloat(item.TotalAmount).toFixed(2)}</td>
                    <td><span class="status-badge status-${item.OrderStatus}">${item.OrderStatus}</span></td>
                </tr>
            `;
        },

        createReportMenuSaleRowHTML(item, totalHotelRevenue) {
            const revenue = parseFloat(item.TotalRevenue);
            const percentage = totalHotelRevenue > 0 ? (revenue / totalHotelRevenue) * 100 : 0;
            return `
                <tr>
                    <td>${item.ItemName}</td>
                    <td>${item.CategoryName}</td>
                    <td>${item.QuantitySold}</td>
                    <td>Rs ${revenue.toFixed(2)}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>${percentage.toFixed(1)}%</span>
                            <div class="progress-bar"><div style="width: ${percentage.toFixed(1)}%"></div></div>
                        </div>
                    </td>
                </tr>
            `;
        },

        createReportStoreSaleRowHTML(item) {
            const saleTime = new Date(item.SaleTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return `
                <tr>
                    <td>${saleTime}</td>
                    <td>${item.CategoryName}</td>
                    <td>${item.ItemDescription || 'N/A'}</td>
                    <td>Rs ${parseFloat(item.TotalAmount).toFixed(2)}</td>
                </tr>
            `;
        },

        createReportStaffRowHTML(item) {
            return `
                <tr>
                    <td>${item.FirstName} ${item.LastName}</td>
                    <td>${item.OrderCount}</td>
                    <td>Rs ${parseFloat(item.TotalRevenue).toFixed(2)}</td>
                </tr>
            `;
        },

        showOrderDetailsModal(orderId) {
            const details = this.data.todays_report.order_details[orderId];
            if (!details) return;

            this.elements.modalTitle.textContent = `Details for Order #${orderId}`;
            let html = '';
            details.forEach(item => {
                html += `
                    <div class="detail-item">
                        <div>
                            <div class="item-name">${item.ItemName}</div>
                            <div class="item-qty">Quantity: ${item.Quantity}</div>
                        </div>
                        <div class="item-subtotal">Rs ${parseFloat(item.Subtotal).toFixed(2)}</div>
                    </div>
                `;
                if (item.SpecialInstructions) {
                    html += `<div style="font-size: 0.85rem; color: var(--text-secondary); padding-left: 0.5rem;"><em>Note: ${item.SpecialInstructions}</em></div>`;
                }
            });
            this.elements.modalBody.innerHTML = html;
            this.elements.modal.style.display = 'flex';
        },

        hideModal() {
            this.elements.modal.style.display = 'none';
        }
    };

    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>
