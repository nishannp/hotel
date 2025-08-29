<?php 
require_once 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <link rel="stylesheet" href="css/analytics_style.css">
</head>
<body>

<div class="main-content">
    <header class="page-header">
        <h1><i class="fas fa-chart-line"></i> Business Analytics</h1>
        <p>A comprehensive overview of your business performance.</p>
    </header>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label for="date-range-preset">Date Range:</label>
            <select id="date-range-preset">
                <option value="today" selected>Today</option>
                <option value="last7">Last 7 Days</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        <div class="filter-group" id="custom-date-range-picker" style="display:none;">
            <input type="date" id="start-date">
            <span>to</span>
            <input type="date" id="end-date">
        </div>
        <button id="btn-apply-filter" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <h4>Total Revenue</h4>
            <p id="kpi-total-revenue">Rs 0.00</p>
            <i class="fas fa-wallet"></i>
        </div>
        <div class="kpi-card">
            <h4>Total Expenses</h4>
            <p id="kpi-total-expenses">Rs 0.00</p>
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="kpi-card profit">
            <h4>Net Profit</h4>
            <p id="kpi-net-profit">Rs 0.00</p>
            <i class="fas fa-coins"></i>
        </div>
        <div class="kpi-card">
            <h4>Hotel Revenue</h4>
            <p id="kpi-hotel-revenue">Rs 0.00</p>
            <i class="fas fa-concierge-bell"></i>
        </div>
        <div class="kpi-card">
            <h4>Store Revenue</h4>
            <p id="kpi-store-revenue">Rs 0.00</p>
            <i class="fas fa-store"></i>
        </div>
        <div class="kpi-card">
            <h4>Avg. Order Value</h4>
            <p id="kpi-avg-order-value">Rs 0.00</p>
            <i class="fas fa-receipt"></i>
        </div>
        <div class="kpi-card">
            <h4>Hotel Orders</h4>
            <p id="kpi-hotel-orders">0</p>
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="kpi-card">
            <h4>Items Sold</h4>
            <p id="kpi-items-sold">0</p>
            <i class="fas fa-box-open"></i>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-container card large-chart">
            <div class="card-header"><h3><i class="fas fa-chart-area"></i> Revenue vs. Expenses</h3></div>
            <div class="card-body">
                <canvas id="revenue-expenses-chart"></canvas>
            </div>
        </div>
        <div class="chart-container card">
            <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Payment Methods</h3></div>
            <div class="card-body">
                <canvas id="payment-methods-chart"></canvas>
            </div>
        </div>
        <div class="chart-container card">
            <div class="card-header"><h3><i class="fas fa-chart-pie"></i> Expense Breakdown</h3></div>
            <div class="card-body">
                <canvas id="expenses-by-category-chart"></canvas>
            </div>
        </div>
        <div class="chart-container card">
            <div class="card-header"><h3><i class="fas fa-user-check"></i> Top Performing Staff</h3></div>
            <div class="card-body">
                <ul id="top-staff-list" class="ranked-list"></ul>
            </div>
        </div>
    </div>

</div>

<div id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/cdn.min.js"></script>
<script src="js/analytics.js"></script>

</body>
</html>
