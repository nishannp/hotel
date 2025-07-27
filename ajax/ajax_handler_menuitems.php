<?php
// ajax/ajax_handler_menuitems.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

// --- Image Crop and Resize Function ---
function cropAndResizeImage($source_path, $destination_path, $size = 300) {
    list($original_width, $original_height, $type) = getimagesize($source_path);

    $image_resource = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image_resource = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $image_resource = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $image_resource = imagecreatefromgif($source_path);
            break;
        default:
            return false; // Unsupported image type
    }

    $new_image = imagecreatetruecolor($size, $size);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    $source_x = 0;
    $source_y = 0;
    $source_width = $original_width;
    $source_height = $original_height;

    // Crop to square from center
    if ($original_width > $original_height) { // Landscape
        $source_width = $original_height;
        $source_x = ($original_width - $original_height) / 2;
    } elseif ($original_height > $original_width) { // Portrait
        $source_height = $original_width;
        $source_y = ($original_height - $original_width) / 2;
    }

    imagecopyresampled($new_image, $image_resource, 0, 0, $source_x, $source_y, $size, $size, $source_width, $source_height);

    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($new_image, $destination_path, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($new_image, $destination_path, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($new_image, $destination_path);
            break;
    }

    imagedestroy($image_resource);
    imagedestroy($new_image);

    return $success;
}


switch ($action) {
    case 'fetchAll':
        $sql = "SELECT mi.*, mc.CategoryName 
                FROM menu_items mi
                LEFT JOIN menu_categories mc ON mi.CategoryID = mc.CategoryID
                ORDER BY mi.Name";
        $result = $conn->query($sql);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            // Preserve the relative path for form submissions
            $row['RelativeImagePath'] = $row['ImageUrl'];
            // Create the full URL for display
            if (!empty($row['ImageUrl'])) {
                $row['ImageUrl'] = UPLOADS_URL . $row['ImageUrl'];
            }
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
        if ($item && !empty($item['ImageUrl'])) {
            $item['FullImageUrl'] = UPLOADS_URL . $item['ImageUrl'];
        }
        $response = ['success' => true, 'data' => $item];
        $stmt->close();
        break;

    case 'getMenuItemIngredients':
        $id = $_GET['id'] ?? 0;
        $stmt = $conn->prepare("SELECT IngredientID, QuantityRequired FROM menu_item_ingredients WHERE MenuItemID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        $response = ['success' => true, 'data' => $ingredients];
        $stmt->close();
        break;

    case 'save':
        $conn->begin_transaction(); // <-- Start Transaction
        try {
            $id = $_POST['menu_item_id'] ?? null;
            $name = trim($_POST['item_name']);
            $desc = trim($_POST['item_description']);
            $price = $_POST['item_price'];
            $category_id = $_POST['item_category'];
            $is_available = isset($_POST['is_available']) ? 1 : 0;
            $image_path = $_POST['existing_image_path'] ?? '';

            if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
                $upload_dir = UPLOADS_DIR . 'menu_items/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if (!empty($id) && !empty($image_path)) {
                    $old_image_full_path = UPLOADS_DIR . $image_path;
                    if (file_exists($old_image_full_path)) unlink($old_image_full_path);
                }

                $file_name = uniqid() . '-' . basename($_FILES["item_image"]["name"]);
                $target_file = $upload_dir . $file_name;
                $image_path = 'menu_items/' . $file_name;

                if (!cropAndResizeImage($_FILES['item_image']['tmp_name'], $target_file)) {
                    throw new Exception('Failed to upload and resize image.');
                }
            }

            $menu_item_id = $id;
            if (empty($id)) { // ADD
                $sql = "INSERT INTO menu_items (Name, Description, Price, CategoryID, IsAvailable, ImageUrl) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiis", $name, $desc, $price, $category_id, $is_available, $image_path);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $menu_item_id = $stmt->insert_id;
                $message = 'Menu item added successfully.';
                $stmt->close();
            } else { // UPDATE
                $sql = "UPDATE menu_items SET Name = ?, Description = ?, Price = ?, CategoryID = ?, IsAvailable = ?, ImageUrl = ? WHERE MenuItemID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdiisi", $name, $desc, $price, $category_id, $is_available, $image_path, $id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $message = 'Menu item updated successfully.';
                $stmt->close();
            }

            // --- Handle Ingredients ---
            // 1. Delete existing ingredients for this menu item
            $stmt_delete = $conn->prepare("DELETE FROM menu_item_ingredients WHERE MenuItemID = ?");
            $stmt_delete->bind_param("i", $menu_item_id);
            if (!$stmt_delete->execute()) throw new Exception($stmt_delete->error);
            $stmt_delete->close();

            // 2. Insert new ingredients
            if (isset($_POST['ingredients'])) {
                $ingredients = json_decode($_POST['ingredients'], true);
                if (is_array($ingredients) && !empty($ingredients)) {
                    $sql_ing = "INSERT INTO menu_item_ingredients (MenuItemID, IngredientID, QuantityRequired) VALUES (?, ?, ?)";
                    $stmt_ing = $conn->prepare($sql_ing);
                    foreach ($ingredients as $ing) {
                        $stmt_ing->bind_param("iid", $menu_item_id, $ing['ingredient_id'], $ing['quantity']);
                        if (!$stmt_ing->execute()) throw new Exception($stmt_ing->error);
                    }
                    $stmt_ing->close();
                }
            }
            
            $conn->commit(); // <-- Commit Transaction
            $response = ['success' => true, 'message' => $message];

        } catch (Exception $e) {
            $conn->rollback(); // <-- Rollback on error
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;

    case 'delete':
        $id = $_POST['id'];
        
        // First, get the image path to delete the file
        $stmt = $conn->prepare("SELECT ImageUrl FROM menu_items WHERE MenuItemID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        if ($item && !empty($item['ImageUrl'])) {
            $image_full_path = UPLOADS_DIR . $item['ImageUrl'];
            if (file_exists($image_full_path)) {
                unlink($image_full_path); // Delete the image file
            }
        }
        $stmt->close();

        // Then, delete the database record. The ON DELETE CASCADE in the DB will handle menu_item_ingredients.
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
