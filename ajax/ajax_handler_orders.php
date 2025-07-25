<?php
// ajax/ajax_handler_orders.php
require_once '../config.php';
header('Content-Type: application/json');

// Fetches all orders for a historical view
$sql = "SELECT o.OrderID, o.OrderTime, o.OrderStatus, o.TotalAmount, t.TableNumber, s.FirstName, s.LastName
        FROM orders o
        JOIN restaurant_tables t ON o.TableID = t.TableID
        JOIN staff s ON o.StaffID = s.StaffID
        ORDER BY o.OrderID DESC
        LIMIT 100"; // Limit to recent 100 to prevent performance issues

$result = $conn->query($sql);
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(['success' => true, 'data' => $orders]);
$conn->close();