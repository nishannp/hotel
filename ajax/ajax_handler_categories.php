<?php
// ajax/ajax_handler_categories.php
require_once '../config.php'; // Note the '..' because we are in a sub-folder

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT CategoryID, CategoryName, Description FROM menu_categories ORDER BY CategoryName";
        $result = $conn->query($sql);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $response = ['success' => true, 'data' => $categories];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT CategoryID, CategoryName, Description FROM menu_categories WHERE CategoryID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $category];
        $stmt->close();
        break;

    case 'add':
        $name = trim($_POST['category_name']);
        $desc = trim($_POST['description']);
        $stmt = $conn->prepare("INSERT INTO menu_categories (CategoryName, Description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Category added successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'update':
        $id = $_POST['category_id'];
        $name = trim($_POST['category_name']);
        $desc = trim($_POST['description']);
        $stmt = $conn->prepare("UPDATE menu_categories SET CategoryName = ?, Description = ? WHERE CategoryID = ?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Category updated successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM menu_categories WHERE CategoryID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Category deleted successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);