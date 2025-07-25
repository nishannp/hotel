<?php
// ajax/ajax_handler_menuitems.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT mi.*, mc.CategoryName 
                FROM menu_items mi
                LEFT JOIN menu_categories mc ON mi.CategoryID = mc.CategoryID
                ORDER BY mi.Name";
        $result = $conn->query($sql);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $response = ['success' => true, 'data' => $items];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE MenuItemID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $item];
        $stmt->close();
        break;

    case 'save': // Handles both add and update
        $id = $_POST['menu_item_id'] ?? null;
        $name = trim($_POST['item_name']);
        $desc = trim($_POST['item_description']);
        $price = $_POST['item_price'];
        $category_id = $_POST['item_category'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $image_url = trim($_POST['image_url']);

        if (empty($id)) { // ADD
            $sql = "INSERT INTO menu_items (Name, Description, Price, CategoryID, IsAvailable, ImageUrl) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiis", $name, $desc, $price, $category_id, $is_available, $image_url);
            $message = 'Menu item added successfully.';
        } else { // UPDATE
            $sql = "UPDATE menu_items SET Name = ?, Description = ?, Price = ?, CategoryID = ?, IsAvailable = ?, ImageUrl = ? WHERE MenuItemID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiisi", $name, $desc, $price, $category_id, $is_available, $image_url, $id);
            $message = 'Menu item updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE MenuItemID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Menu item deleted successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);