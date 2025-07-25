<?php 
// dashboard.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Live Dashboard';</script>";
?>

<div class="card">
    <h3>System Overview (Today)</h3>
    <p>Live statistics for today's operations. This data refreshes automatically.</p>
</div>

<div class="cards-container">
    <!-- Stat Card 1: Revenue Today -->
    <div class="card stat-card">
        <div class="icon-container bg-green">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="info">
            <h4 id="stat-revenue">$0.00</h4>
            <p>Revenue Today</p>
        </div>
    </div>

    <!-- Stat Card 2: Orders Today -->
    <div class="card stat-card">
        <div class="icon-container bg-blue">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="info">
            <h4 id="stat-orders">0</h4>
            <p>Orders Today</p>
        </div>
    </div>
    
    <!-- Stat Card 3: Occupied Tables -->
    <div class="card stat-card">
        <div class="icon-container bg-orange">
            <i class="fa-solid fa-chair"></i>
        </div>
        <div class="info">
            <h4 id="stat-tables">0</h4>
            <p>Currently Occupied Tables</p>
        </div>
    </div>

    <!-- Stat Card 4: Items Low on Stock -->
    <div class="card stat-card">
        <div class="icon-container bg-red">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="info">
            <h4 id="stat-low-stock">0</h4>
            <p>Items Low on Stock</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchDashboardStats() {
        fetch('ajax/ajax_handler_dashboard.php')
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const stats = data.data;
                    document.getElementById('stat-revenue').textContent = `$${parseFloat(stats.todays_revenue || 0).toFixed(2)}`;
                    document.getElementById('stat-orders').textContent = stats.todays_orders || 0;
                    document.getElementById('stat-tables').textContent = stats.pending_tables || 0;
                    document.getElementById('stat-low-stock').textContent = stats.low_stock_items || 0;
                }
            })
            .catch(error => console.error('Failed to fetch dashboard stats:', error));
    }

    // Fetch stats on page load
    fetchDashboardStats();

    // Optionally, refresh the stats every 30 seconds
    setInterval(fetchDashboardStats, 30000); 
});
</script>

<?php 
require_once 'includes/footer.php'; 
?>