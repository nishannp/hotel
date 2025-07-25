<?php 
// orders.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'All Orders History';</script>";
?>

<div class="card">
    <div class="card-header">
        <h3>Recent Orders</h3>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Order ID</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Table</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Staff</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Status</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Total</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Time</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('ordersTableBody');
    fetch('ajax/ajax_handler_orders.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                tableBody.innerHTML = '';
                data.data.forEach(order => {
                    let statusClass = '';
                    if (order.OrderStatus === 'Completed') statusClass = 'color:green;';
                    if (order.OrderStatus === 'Cancelled') statusClass = 'color:red;';
                    
                    tableBody.innerHTML += `
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">#${order.OrderID}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">${order.TableNumber}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">${order.FirstName}</td>
                            <td style="padding: 12px; border: 1px solid #ddd; font-weight:bold; ${statusClass}">${order.OrderStatus}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">$${parseFloat(order.TotalAmount).toFixed(2)}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">${new Date(order.OrderTime).toLocaleString()}</td>
                        </tr>
                    `;
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No orders found.</td></tr>';
            }
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>