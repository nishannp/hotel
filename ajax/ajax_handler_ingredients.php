<?php
// ajax/ajax_handler_ingredients.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        // JOIN to get stock quantity from the inventory table
        $sql = "SELECT i.IngredientID, i.Name, i.UnitOfMeasure, inv.QuantityInStock 
                FROM ingredients i
                LEFT JOIN inventory inv ON i.IngredientID = inv.IngredientID
                ORDER BY i.Name";
        $result = $conn->query($sql);
        $ingredients = [];
        while ($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
        $response = ['success' => true, 'data' => $ingredients];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT i.IngredientID, i.Name, i.UnitOfMeasure, inv.QuantityInStock, inv.SupplierInfo, i.ImageUrl 
                                FROM ingredients i
                                LEFT JOIN inventory inv ON i.IngredientID = inv.IngredientID
                                WHERE i.IngredientID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ingredient = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $ingredient];
        $stmt->close();
        break;

    case 'save':
        $conn->begin_transaction(); // Start transaction for data integrity

        try {
            $id = $_POST['ingredient_id'] ?? null;
            $name = trim($_POST['name']);
            $unit = trim($_POST['unit_of_measure']);
            $quantity = $_POST['quantity_in_stock'];
            $supplier = trim($_POST['supplier_info']);
            $image_url = trim($_POST['image_url']);
            $message = '';
            
            if (empty($id)) { // ADD
                // Step 1: Insert into ingredients table
                $stmt1 = $conn->prepare("INSERT INTO ingredients (Name, UnitOfMeasure, ImageUrl) VALUES (?, ?, ?)");
                $stmt1->bind_param("sss", $name, $unit, $image_url);
                $stmt1->execute();
                $new_id = $stmt1->insert_id;
                $stmt1->close();
                
                // Step 2: Insert into inventory table
                $stmt2 = $conn->prepare("INSERT INTO inventory (IngredientID, QuantityInStock, SupplierInfo) VALUES (?, ?, ?)");
                $stmt2->bind_param("ids", $new_id, $quantity, $supplier);
                $stmt2->execute();
                $stmt2->close();
                $message = 'Ingredient added successfully.';
            } else { // UPDATE
                // Step 1: Update ingredients table
                $stmt1 = $conn->prepare("UPDATE ingredients SET Name = ?, UnitOfMeasure = ?, ImageUrl = ? WHERE IngredientID = ?");
                $stmt1->bind_param("sssi", $name, $unit, $image_url, $id);
                $stmt1->execute();
                $stmt1->close();

                // Step 2: Update inventory table
                $stmt2 = $conn->prepare("UPDATE inventory SET QuantityInStock = ?, SupplierInfo = ? WHERE IngredientID = ?");
                $stmt2->bind_param("dsi", $quantity, $supplier, $id);
                $stmt2->execute();
                $stmt2->close();
                $message = 'Ingredient updated successfully.';
            }
            
            $conn->commit(); // Commit transaction if all queries succeed
            $response = ['success' => true, 'message' => $message];

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback(); // Rollback on error
            if($conn->errno == 1062) {
                 $response['message'] = 'Error: An ingredient with this name already exists.';
            } else {
                 $response['message'] = 'Database transaction failed: ' . $exception->getMessage();
            }
        }
        break;

    case 'delete':
        $id = $_POST['id'];
        // Deleting from 'ingredients' will cascade and delete from 'inventory'
        $stmt = $conn->prepare("DELETE FROM ingredients WHERE IngredientID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Ingredient deleted successfully.'];
        } else {
             if($conn->errno == 1451) {
                 $response['message'] = 'Cannot delete this ingredient. It is used in menu items.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);