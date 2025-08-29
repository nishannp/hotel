document.addEventListener('DOMContentLoaded', function() {

    // --- STATE & CONFIG ---
    const API_URL = 'ajax/ajax_handler_analytics.php';
    let charts = {}; // To hold chart instances for updates

    // --- UI SELECTORS ---
    const ui = {
        kpi: {
            totalRevenue: document.getElementById('kpi-total-revenue'),
            totalExpenses: document.getElementById('kpi-total-expenses'),
            netProfit: document.getElementById('kpi-net-profit'),
            hotelRevenue: document.getElementById('kpi-hotel-revenue'),
            storeRevenue: document.getElementById('kpi-store-revenue'),
            avgOrderValue: document.getElementById('kpi-avg-order-value'),
            hotelOrders: document.getElementById('kpi-hotel-orders'),
            itemsSold: document.getElementById('kpi-items-sold'),
        },
        filters: {
            preset: document.getElementById('date-range-preset'),
            customPicker: document.getElementById('custom-date-range-picker'),
            startDate: document.getElementById('start-date'),
            endDate: document.getElementById('end-date'),
            applyBtn: document.getElementById('btn-apply-filter'),
        },
        lists: {
            topStaff: document.getElementById('top-staff-list'),
        },
        toast: document.getElementById('toast'),
    };

    // --- CHART.JS GLOBAL CONFIG ---
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = 'var(--text-muted)';

    // --- API & DATA HANDLING ---
    async function fetchData(startDate, endDate) {
        showToast('Loading analytics...', false, true);
        try {
            const url = new URL(API_URL, window.location.href);
            url.searchParams.append('start_date', startDate);
            url.searchParams.append('end_date', endDate);
            
            const response = await fetch(url.toString());
            const result = await response.json();

            if (!result.success) throw new Error(result.message);
            
            updateDashboard(result.data);
            showToast('Analytics loaded!', false);

        } catch (error) {
            showToast(error.message, true);
            console.error('Analytics Error:', error);
        }
    }

    // --- UPDATE DASHBOARD ---
    function updateDashboard(data) {
        const formatCurrency = (value) => `Rs ${parseFloat(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        
        // KPIs
        ui.kpi.totalRevenue.textContent = formatCurrency(data.kpi.total_revenue);
        ui.kpi.totalExpenses.textContent = formatCurrency(data.kpi.total_expenses);
        const netProfit = parseFloat(data.kpi.net_profit);
        ui.kpi.netProfit.textContent = formatCurrency(netProfit);
        ui.kpi.netProfit.style.color = netProfit >= 0 ? 'var(--success-color)' : 'var(--danger-color)';
        
        ui.kpi.hotelRevenue.textContent = formatCurrency(data.kpi.hotel_revenue);
        ui.kpi.storeRevenue.textContent = formatCurrency(data.kpi.store_revenue);
        ui.kpi.avgOrderValue.textContent = formatCurrency(data.kpi.avg_order_value);
        ui.kpi.hotelOrders.textContent = data.kpi.hotel_orders;
        ui.kpi.itemsSold.textContent = data.kpi.total_items_sold;

        // Charts & Lists
        updateRevenueExpensesChart(data.charts.revenue_vs_expenses);
        updatePaymentMethodsChart(data.charts.payment_methods);
        updateExpenseBreakdownChart(data.charts.expenses_by_category);
        renderTopStaff(data.lists.top_staff);
    }

    // --- CHART & LIST RENDERING ---
    function renderChart(canvasId, type, data, options = {}) {
        if (charts[canvasId]) charts[canvasId].destroy();
        const ctx = document.getElementById(canvasId).getContext('2d');
        charts[canvasId] = new Chart(ctx, { type, data, options });
    }

    function updateRevenueExpensesChart(data) {
        renderChart('revenue-expenses-chart', 'line', {
            labels: data.map(d => d.date),
            datasets: [
                { label: 'Revenue', data: data.map(d => d.daily_revenue), borderColor: 'var(--primary-color)', tension: 0.3, fill: false },
                { label: 'Expenses', data: data.map(d => d.daily_expenses), borderColor: 'var(--danger-color)', tension: 0.3, fill: false }
            ]
        });
    }

    function updatePaymentMethodsChart(data) {
        renderChart('payment-methods-chart', 'doughnut', {
            labels: data.map(d => d.PaymentMethod),
            datasets: [{
                data: data.map(d => d.total),
                backgroundColor: ['#367bf5', '#34c38f', '#f1b44c', '#f46a6a', '#50a5f1'],
                borderWidth: 0
            }]
        });
    }
    
    function updateExpenseBreakdownChart(data) {
        renderChart('expenses-by-category-chart', 'doughnut', {
            labels: data.map(d => d.name),
            datasets: [{
                data: data.map(d => d.total),
                backgroundColor: ['#50a5f1', '#7f8c8d', '#f1b44c', '#34c38f', '#f46a6a', '#367bf5'],
                borderWidth: 0
            }]
        });
    }

    function renderTopStaff(staff) {
        ui.lists.topStaff.innerHTML = '';
        if (staff.length === 0) {
            ui.lists.topStaff.innerHTML = '<p>No staff sales data for this period.</p>';
            return;
        }
        staff.forEach((s, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <span class="rank-badge rank-${index + 1}">${index + 1}</span>
                <div class="staff-details">
                    <div class="name">${s.FirstName} ${s.LastName}</div>
                    <div class="sales">Rs ${parseFloat(s.total_sales).toLocaleString()} in sales</div>
                </div>
            `;
            ui.lists.topStaff.appendChild(li);
        });
    }

    // --- EVENT HANDLERS ---
    function handleFilterChange() {
        ui.filters.customPicker.style.display = ui.filters.preset.value === 'custom' ? 'flex' : 'none';
    }

    function applyDateFilter() {
        const preset = ui.filters.preset.value;
        let startDate, endDate;
        const today = new Date();
        const formatDate = (date) => date.toISOString().split('T')[0];

        switch(preset) {
            case 'last7':
                startDate = formatDate(dateFns.subDays(today, 6));
                endDate = formatDate(today);
                break;
            case 'this_month':
                startDate = formatDate(dateFns.startOfMonth(today));
                endDate = formatDate(dateFns.endOfMonth(today));
                break;
            case 'last_month':
                const lastMonth = dateFns.subMonths(today, 1);
                startDate = formatDate(dateFns.startOfMonth(lastMonth));
                endDate = formatDate(dateFns.endOfMonth(lastMonth));
                break;
            case 'custom':
                startDate = ui.filters.startDate.value;
                endDate = ui.filters.endDate.value;
                if (!startDate || !endDate) {
                    showToast('Please select a start and end date.', true);
                    return;
                }
                break;
            case 'today':
            default:
                startDate = endDate = formatDate(today);
                break;
        }
        fetchData(startDate, endDate);
    }

    // --- HELPERS ---
    function showToast(message, isError = false, isSticky = false) {
        ui.toast.textContent = message;
        ui.toast.className = `show ${isError ? 'error' : 'success'}`;
        if (!isSticky) {
            setTimeout(() => { ui.toast.className = ''; }, 3000);
        }
    }

    // --- INITIALIZATION ---
    function init() {
        ui.filters.preset.addEventListener('change', handleFilterChange);
        ui.filters.applyBtn.addEventListener('click', applyDateFilter);
        
        const today = new Date();
        ui.filters.endDate.value = today.toISOString().split('T')[0];
        ui.filters.startDate.value = today.toISOString().split('T')[0];

        applyDateFilter(); // Load data for "Today" by default
    }

    init();
});
