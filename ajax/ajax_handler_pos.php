<?php
// ajax/ajax_handler_pos.php - Final, simplified, and corrected action handler.
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action provided.'];

switch ($action) {
    case 'createOrder':
        // Check for required POST variables directly.
        if (empty($_POST['table_id']) || empty($_POST['staff_id'])) {
            $response['message'] = 'Error: Table ID or Staff ID was not received by the server.';
            break;
        }
        $table_id = $_POST['table_id'];
        $staff_id = $_POST['staff_id'];

        $sql = "INSERT INTO orders (TableID, StaffID) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $table_id, $staff_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'order_id' => $stmt->insert_id, 'message' => 'Order created!'];
        } else {
            $response['message'] = 'Database Error: Failed to create order. ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'getOrderDetails':
        // This logic is generally safe and correct.
        $order_id = $_GET['order_id'] ?? 0;
        if (empty($order_id)) { $response['message'] = 'No Order ID provided.'; break; }
        
        $data = [];
        $stmt_order = $conn->prepare("SELECT OrderID, TableID, StaffID, OrderTime, OrderStatus, TotalAmount FROM orders WHERE OrderID = ?");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $data['orderInfo'] = $stmt_order->get_result()->fetch_assoc();
        $stmt_order->close();

        $stmt_items = $conn->prepare("SELECT od.OrderDetailID, od.MenuItemID, od.Quantity, od.Subtotal, mi.Name 
                                      FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
                                      WHERE od.OrderID = ? ORDER BY od.OrderDetailID");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        $items = [];
        while($row = $result_items->fetch_assoc()) { $items[] = $row; }
        $data['items'] = $items;
        $stmt_items->close();
        
        $response = ['success' => true, 'data' => $data];
        break;

    case 'addItemToOrder':
        // Explicitly check for all required POST variables.
        if (empty($_POST['order_id']) || empty($_POST['menu_item_id']) || empty($_POST['quantity'])) {
            $response['message'] = 'Error: Order ID, Menu Item, or Quantity was not received by the server.';
            break;
        }
        $order_id = $_POST['order_id'];
        $menu_item_id = $_POST['menu_item_id'];
        $quantity_to_add = $_POST['quantity'];

        // Logic to combine with existing item or add new
        $stmt_check = $conn->prepare("SELECT OrderDetailID, Quantity FROM order_details WHERE OrderID = ? AND MenuItemID = ?");
        $stmt_check->bind_param("ii", $order_id, $menu_item_id);
        $stmt_check->execute();
        $existing_item = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($existing_item) {
            $new_quantity = $existing_item['Quantity'] + $quantity_to_add;
            $_POST['order_detail_id'] = $existing_item['OrderDetailID'];
            $_POST['new_quantity'] = $new_quantity;
            goto update_quantity_logic; // Use the robust update logic
        }

        // Insert new item logic
        try {
            $stmt_price = $conn->prepare("SELECT Price FROM menu_items WHERE MenuItemID = ?");
            $stmt_price->bind_param("i", $menu_item_id);
            $stmt_price->execute();
            $price_result = $stmt_price->get_result()->fetch_assoc();
            $stmt_price->close();
            $subtotal = $price_result['Price'] * $quantity_to_add;

            $stmt_insert = $conn->prepare("INSERT INTO order_details (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iiid", $order_id, $menu_item_id, $quantity_to_add, $subtotal);
            $stmt_insert->execute();
            $stmt_insert->close();
            $response = ['success' => true, 'message' => 'Item added.'];
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1644) { $response['message'] = $e->getMessage(); }
            else { $response['message'] = "DB Error: " . $e->getMessage(); }
        }
        break;

    case 'updateItemQuantity':
        update_quantity_logic: // Label for goto from addItemToOrder
        if (empty($_POST['order_detail_id']) || !isset($_POST['new_quantity'])) {
            $response['message'] = 'Error: Missing data for quantity update.';
            break;
        }
        $order_detail_id = $_POST['order_detail_id'];
        $new_quantity = $_POST['new_quantity'];

        if ($new_quantity <= 0) {
            $stmt_delete = $conn->prepare("DELETE FROM order_details WHERE OrderDetailID = ?");
            $stmt_delete->bind_param("i", $order_detail_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            $response = ['success' => true, 'message' => 'Item removed.'];
            break;
        }
        
        // This robust transaction logic is the correct way to handle triggers.
        $conn->begin_transaction();
        try {
            // Get necessary info before deleting
            $stmt_old = $conn->prepare("SELECT OrderID, MenuItemID, (SELECT Price FROM menu_items WHERE MenuItemID = od.MenuItemID) as Price FROM order_details od WHERE OrderDetailID = ?");
            $stmt_old->bind_param("i", $order_detail_id);
            $stmt_old->execute();
            $old_details = $stmt_old->get_result()->fetch_assoc();
            $stmt_old->close();

            $new_subtotal = $old_details['Price'] * $new_quantity;

            // Delete old entry (restocks inventory)
            $stmt_delete = $conn->prepare("DELETE FROM order_details WHERE OrderDetailID = ?");
            $stmt_delete->bind_param("i", $order_detail_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Insert new entry (decrements inventory)
            $stmt_reinsert = $conn->prepare("INSERT INTO order_details (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, ?, ?)");
            $stmt_reinsert->bind_param("iiid", $old_details['OrderID'], $old_details['MenuItemID'], $new_quantity, $new_subtotal);
            $stmt_reinsert->execute();
            $stmt_reinsert->close();
            
            $conn->commit();
            $response = ['success' => true, 'message' => 'Quantity updated.'];
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            if ($e->getCode() == 1644) { $response['message'] = $e->getMessage(); } 
            else { $response['message'] = "DB Error: " . $e->getMessage(); }
        }
        break;
    
    case 'updateOrderStatus':
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE orders SET OrderStatus = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $status, $_POST['order_id']);
        if ($stmt->execute()) $response = ['success' => true];
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);