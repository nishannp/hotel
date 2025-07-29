<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Helper function to handle file uploads
function handle_image_upload($cropped_data, $existing_path = '') {
    if (empty($cropped_data)) {
        return ['success' => true, 'path' => $existing_path];
    }

    // Path setup
    $upload_dir = '../uploads/store_items/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Delete old image if a new one is uploaded and an old one exists
    if (!empty($existing_path) && file_exists('..' . $existing_path)) {
        unlink('..' . $existing_path);
    }

    // Process the new base64 image data
    list($type, $data) = explode(';', $cropped_data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);

    $filename = 'store_' . uniqid() . '.jpg';
    $filepath = $upload_dir . $filename;
    $relative_path = '/uploads/store_items/' . $filename;

    if (file_put_contents($filepath, $data)) {
        return ['success' => true, 'path' => $relative_path];
    } else {
        return ['success' => false, 'message' => 'Failed to save the cropped image.'];
    }
}


header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT 
                    si.StoreItemID, si.Name, si.Description, si.Price, si.IsAvailable,
                    si.ImageUrl AS RelativeImagePath,
                    CONCAT('/hotel', si.ImageUrl) AS ImageUrl,
                    sc.CategoryID, sc.CategoryName
                FROM store_items si
                LEFT JOIN store_item_categories sc ON si.CategoryID = sc.CategoryID
                ORDER BY sc.CategoryName, si.Name";
        $result = $conn->query($sql);
        $items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $response = ['success' => true, 'data' => $items];
        break;

    case 'save':
        $id = $_POST['store_item_id'] ?? null;
        $name = trim($_POST['item_name'] ?? '');
        $category_id = $_POST['item_category'] ?? null;
        $price = $_POST['item_price'] ?? 0;
        $description = trim($_POST['item_description'] ?? '');
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $cropped_image_data = $_POST['cropped_image_data'] ?? '';
        $existing_image_path = $_POST['existing_image_path'] ?? '';

        if (empty($name) || empty($category_id) || !is_numeric($price)) {
            $response['message'] = 'Name, Category, and a valid Price are required.';
            echo json_encode($response);
            exit;
        }

        $upload_result = handle_image_upload($cropped_image_data, $existing_image_path);
        if (!$upload_result['success']) {
            echo json_encode($upload_result);
            exit;
        }
        $image_path = $upload_result['path'];

        if (empty($id)) { // Insert new item
            $sql = "INSERT INTO store_items (Name, CategoryID, Price, Description, IsAvailable, ImageUrl) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siddis", $name, $category_id, $price, $description, $is_available, $image_path);
            $message = 'Store item added successfully.';
        } else { // Update existing item
            $sql = "UPDATE store_items SET Name=?, CategoryID=?, Price=?, Description=?, IsAvailable=?, ImageUrl=? WHERE StoreItemID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siddisi", $name, $category_id, $price, $description, $is_available, $image_path, $id);
            $message = 'Store item updated successfully.';
        }

        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            // First, get the image path to delete the file
            $stmt = $conn->prepare("SELECT ImageUrl FROM store_items WHERE StoreItemID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['ImageUrl']) && file_exists('..' . $row['ImageUrl'])) {
                    unlink('..' . $row['ImageUrl']);
                }
            }
            $stmt->close();

            // Then, delete the database record
            $stmt = $conn->prepare("DELETE FROM store_items WHERE StoreItemID = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Store item deleted successfully.'];
            } else {
                $response['message'] = 'Error deleting item: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid ID provided.';
        }
        break;
}

$conn->close();
echo json_encode($response);
