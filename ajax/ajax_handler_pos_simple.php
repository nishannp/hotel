<?php
// ajax/ajax_handler_pos_simple.php
// A new, simplified handler for guaranteed correctness.
require_once '../config.php';
header('Content-Type: application/json');

$response = [
    'success' => true,
    'tables' => [],
    'active_orders' => []
];

// Query 1: Get ALL tables from the database. No joins, no complexity.
$tables_result = $conn->query("SELECT TableID, TableNumber, Status FROM restaurant_tables ORDER BY TableNumber");
if ($tables_result) {
    while ($row = $tables_result->fetch_assoc()) {
        $response['tables'][] = $row;
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to fetch tables.';
    echo json_encode($response);
    exit;
}

// Query 2: Get ALL currently active orders.
$orders_result = $conn->query("SELECT OrderID, TableID, StaffID, OrderStatus, TotalAmount FROM orders WHERE OrderStatus IN ('Pending', 'In-Progress')");
if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        // Use TableID as the key for easy lookup in JavaScript
        $response['active_orders'][$row['TableID']] = $row;
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Failed to fetch active orders.';
    echo json_encode($response);
    exit;
}

echo json_encode($response);
$conn->close();