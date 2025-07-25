<?php
// ajax/ajax_handler_dashboard.php
require_once '../config.php';
header('Content-Type: application/json');

$stats = [];
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');

// 1. Today's Revenue
$result = $conn->query("SELECT SUM(AmountPaid) as total FROM payments WHERE PaymentTime BETWEEN '$today_start' AND '$today_end'");
$stats['todays_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// 2. Today's Orders
$result = $conn->query("SELECT COUNT(OrderID) as count FROM orders WHERE OrderTime BETWEEN '$today_start' AND '$today_end'");
$stats['todays_orders'] = $result->fetch_assoc()['count'] ?? 0;

// 3. Pending (Occupied) Tables
$result = $conn->query("SELECT COUNT(TableID) as count FROM restaurant_tables WHERE Status = 'Occupied'");
$stats['pending_tables'] = $result->fetch_assoc()['count'] ?? 0;

// 4. Items Low on Stock (e.g., less than 10 units)
$low_stock_threshold = 10;
$result = $conn->query("SELECT COUNT(IngredientID) as count FROM inventory WHERE QuantityInStock < $low_stock_threshold");
$stats['low_stock_items'] = $result->fetch_assoc()['count'] ?? 0;

echo json_encode(['success' => true, 'data' => $stats]);
$conn->close();