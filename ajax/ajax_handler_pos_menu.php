<?php
// ajax/ajax_handler_pos_menu.php
// This new handler provides all data needed for the visual POS menu.
require_once '../config.php';
header('Content-Type: application/json');

$response = [
    'success' => true,
    'categories' => [],
    'menu_items' => []
];

// 1. Get all menu categories
$cat_result = $conn->query("SELECT CategoryID, CategoryName FROM menu_categories ORDER BY CategoryName");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $response['categories'][] = $row;
    }
}

// 2. Get all available menu items
$item_result = $conn->query("SELECT MenuItemID, CategoryID, Name, Price, ImageUrl FROM menu_items WHERE IsAvailable = 1 ORDER BY Name");
if ($item_result) {
    while ($row = $item_result->fetch_assoc()) {
        $response['menu_items'][] = $row;
    }
}

echo json_encode($response);
$conn->close();