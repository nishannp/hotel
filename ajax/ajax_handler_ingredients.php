<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ajax/ajax_handler_ingredients.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

// --- Image Crop and Resize Function ---
function cropAndResizeImage($source_path, $destination_path, $size = 300) {
    list($original_width, $original_height, $type) = getimagesize($source_path);

    $image_resource = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $image_resource = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG: $image_resource = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF: $image_resource = imagecreatefromgif($source_path); break;
        default: return false;
    }

    $new_image = imagecreatetruecolor($size, $size);
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    $source_x = 0; $source_y = 0;
    $source_width = $original_width; $source_height = $original_height;
    if ($original_width > $original_height) {
        $source_width = $original_height;
        $source_x = ($original_width - $original_height) / 2;
    } elseif ($original_height > $original_width) {
        $source_height = $original_width;
        $source_y = ($original_height - $original_width) / 2;
    }

    imagecopyresampled($new_image, $image_resource, 0, 0, $source_x, $source_y, $size, $size, $source_width, $source_height);

    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG: $success = imagejpeg($new_image, $destination_path, 90); break;
        case IMAGETYPE_PNG: $success = imagepng($new_image, $destination_path, 9); break;
        case IMAGETYPE_GIF: $success = imagegif($new_image, $destination_path); break;
    }

    imagedestroy($image_resource);
    imagedestroy($new_image);
    return $success;
}

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT i.IngredientID, i.Name, i.UnitOfMeasure, i.ImageUrl, inv.QuantityInStock 
                FROM ingredients i
                LEFT JOIN inventory inv ON i.IngredientID = inv.IngredientID
                ORDER BY i.Name";
        $result = $conn->query($sql);
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $row['RelativeImagePath'] = $row['ImageUrl'];
            if (!empty($row['ImageUrl'])) {
                $row['ImageUrl'] = UPLOADS_URL . $row['ImageUrl'];
            }
            $ingredients[] = $row;
        }
        $response = ['success' => true, 'data' => $ingredients];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT i.IngredientID, i.Name, i.UnitOfMeasure, i.ImageUrl, inv.QuantityInStock, inv.SupplierInfo
                                FROM ingredients i
                                LEFT JOIN inventory inv ON i.IngredientID = inv.IngredientID
                                WHERE i.IngredientID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ingredient = $result->fetch_assoc();
        if ($ingredient && !empty($ingredient['ImageUrl'])) {
            $ingredient['FullImageUrl'] = UPLOADS_URL . $ingredient['ImageUrl'];
        }
        $response = ['success' => true, 'data' => $ingredient];
        $stmt->close();
        break;

    case 'save':
        $conn->begin_transaction();
        try {
            $id = $_POST['ingredient_id'] ?? null;
            $name = trim($_POST['name']);
            $unit = trim($_POST['unit_of_measure']);
            $quantity = $_POST['quantity_in_stock'] ?? 0;
            $supplier = trim($_POST['supplier_info']);
            $image_path = $_POST['existing_image_path'] ?? '';

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_dir = UPLOADS_DIR . 'ingredients/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if (!empty($id) && !empty($image_path)) {
                     $old_image_full_path = UPLOADS_DIR . $image_path;
                     if (file_exists($old_image_full_path)) unlink($old_image_full_path);
                }

                $file_name = uniqid() . '-' . basename($_FILES["image"]["name"]);
                $target_file = $upload_dir . $file_name;
                $image_path = 'ingredients/' . $file_name;

                if (!cropAndResizeImage($_FILES['image']['tmp_name'], $target_file)) {
                     throw new Exception('Failed to upload and resize image.');
                }
            }

            if (empty($id)) { // ADD
                $stmt1 = $conn->prepare("INSERT INTO ingredients (Name, UnitOfMeasure, ImageUrl) VALUES (?, ?, ?)");
                $stmt1->bind_param("sss", $name, $unit, $image_path);
                $stmt1->execute();
                $new_id = $stmt1->insert_id;
                $stmt1->close();
                
                $stmt2 = $conn->prepare("INSERT INTO inventory (IngredientID, QuantityInStock, SupplierInfo) VALUES (?, ?, ?)");
                $stmt2->bind_param("ids", $new_id, $quantity, $supplier);
                $stmt2->execute();
                $stmt2->close();
                $message = 'Ingredient added successfully.';
            } else { // UPDATE
                $stmt1 = $conn->prepare("UPDATE ingredients SET Name = ?, UnitOfMeasure = ?, ImageUrl = ? WHERE IngredientID = ?");
                $stmt1->bind_param("sssi", $name, $unit, $image_path, $id);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $conn->prepare("UPDATE inventory SET QuantityInStock = ?, SupplierInfo = ? WHERE IngredientID = ?");
                $stmt2->bind_param("dsi", $quantity, $supplier, $id);
                $stmt2->execute();
                $stmt2->close();
                $message = 'Ingredient updated successfully.';
            }
            
            $conn->commit();
            $response = ['success' => true, 'message' => $message];

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
            if ($e instanceof mysqli_sql_exception && $conn->errno == 1062) {
                 $response['message'] = 'Error: An ingredient with this name already exists.';
            }
        }
        break;

    case 'delete':
        $id = $_POST['id'];
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT ImageUrl FROM ingredients WHERE IngredientID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($item = $result->fetch_assoc()) {
                if (!empty($item['ImageUrl'])) {
                    $image_full_path = UPLOADS_DIR . $item['ImageUrl'];
                    if (file_exists($image_full_path)) unlink($image_full_path);
                }
            }
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM ingredients WHERE IngredientID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $response = ['success' => true, 'message' => 'Ingredient deleted successfully.'];
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
            if ($e instanceof mysqli_sql_exception && $conn->errno == 1451) {
                 $response['message'] = 'Cannot delete. This ingredient is used in one or more menu items.';
            }
        }
        break;
}

$conn->close();
echo json_encode($response);
