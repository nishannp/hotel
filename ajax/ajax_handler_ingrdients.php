<?php
// ajax/ajax_handler_menu_ingredients.php
require_once '../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchForMenuItem':
        $menu_id = $_POST['menu_item_id'] ?? 0;
        $sql = "SELECT mii.IngredientID, i.Name, mii.QuantityRequired, i.UnitOfMeasure 
                FROM menu_item_ingredients mii
                JOIN ingredients i ON mii.IngredientID = i.IngredientID
                WHERE mii.MenuItemID = ? ORDER BY i.Name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        $response = ['success' => true, 'data' => $ingredients];
        $stmt->close();
        break;
        
    case 'linkIngredient':
        $menu_id = $_POST['menu_item_id'];
        $ing_id = $_POST['ingredient_id'];
        $qty = $_POST['quantity_required'];
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE to prevent errors and allow easy updates
        $sql = "INSERT INTO menu_item_ingredients (MenuItemID, IngredientID, QuantityRequired) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE QuantityRequired = VALUES(QuantityRequired)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $menu_id, $ing_id, $qty);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Ingredient linked successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;
        
    case 'unlinkIngredient':
        $menu_id = $_POST['menu_item_id'];
        $ing_id = $_POST['ingredient_id'];
        $sql = "DELETE FROM menu_item_ingredients WHERE MenuItemID = ? AND IngredientID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $menu_id, $ing_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Ingredient unlinked successfully.'];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);