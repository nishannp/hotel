<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action specified.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT CategoryID, CategoryName FROM store_item_categories ORDER BY CategoryName";
        $result = $conn->query($sql);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $response = ['success' => true, 'data' => $categories];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("SELECT CategoryID, CategoryName FROM store_item_categories WHERE CategoryID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $category = $result->fetch_assoc();
            if ($category) {
                $response = ['success' => true, 'data' => $category];
            } else {
                $response['message'] = 'Category not found.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid ID provided for fetchSingle.';
        }
        break;

    case 'save':
        $id = $_POST['category_id'] ?? null;
        $name = trim($_POST['category_name'] ?? '');

        if (empty($name)) {
            $response['message'] = 'Category name cannot be empty.';
            break;
        }

        try {
            if (empty($id)) { // ADD
                $sql = "INSERT INTO store_item_categories (CategoryName) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $name);
                $message = 'Category added successfully.';
            } else { // UPDATE
                $sql = "UPDATE store_item_categories SET CategoryName = ? WHERE CategoryID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $name, $id);
                $message = 'Category updated successfully.';
            }
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => $message];
            } else {
                // Check for unique constraint violation
                if ($conn->errno == 1062) { // 1062 is the MySQL error number for duplicate entry
                    $response['message'] = 'A category with this name already exists.';
                } else {
                    $response['message'] = 'Database error: ' . $stmt->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = 'An exception occurred: ' . $e->getMessage();
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM store_item_categories WHERE CategoryID = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Category and its items deleted successfully.'];
            } else {
                $response['message'] = 'Error deleting category: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid ID provided for deletion.';
        }
        break;
}

$conn->close();
echo json_encode($response);
