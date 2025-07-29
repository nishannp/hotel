<?php
/**
 * ajax/ajax_handler_pos.php
 * * Refactored backend handler for the Point of Sale (POS) system.
 * Handles all asynchronous requests from the POS interface with improved structure and error handling.
 * * @version 4.1 - Fixed 'undefined added' bug.
 */

// --- INITIALIZATION ---
session_start();
header('Content-Type: application/json');
// Ensure this path is correct for your project structure
require_once '../config.php'; 

// --- UTILITY FUNCTIONS ---

/**
 * Sends a JSON success response and terminates the script.
 * @param array $data Additional data to include in the response.
 */
function send_success(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

/**
 * Sends a JSON error response and terminates the script.
 * @param string $message The error message.
 * @param int $code The HTTP status code (optional).
 */
function send_error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Fetches complete details for a specific order, including items.
 * @param mysqli $conn The database connection.
 * @param int $order_id The ID of the order to fetch.
 * @return array|null The order details or null if not found.
 */
function get_order_details(mysqli $conn, int $order_id): ?array {
    $details = [];

    // Fetch main order info
    $stmt_order = $conn->prepare("SELECT OrderID, TableID, StaffID, OrderTime, OrderStatus, TotalAmount FROM orders WHERE OrderID = ?");
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $order_result = $stmt_order->get_result();
    $details['orderInfo'] = $order_result->fetch_assoc();
    $stmt_order->close();

    if (!$details['orderInfo']) {
        return null; // Order not found
    }

    // Fetch order items
    $stmt_items = $conn->prepare(
        "SELECT od.OrderDetailID, od.MenuItemID, od.Quantity, od.Subtotal, mi.Name 
         FROM order_details od 
         JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
         WHERE od.OrderID = ? ORDER BY od.OrderDetailID"
    );
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();
    $details['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    return $details;
}

/**
 * Recalculates and updates the total amount for an order.
 * @param mysqli $conn The database connection.
 * @param int $order_id The ID of the order to update.
 */
function update_order_total(mysqli $conn, int $order_id): void {
    $conn->query("
        UPDATE orders o SET o.TotalAmount = (
            SELECT COALESCE(SUM(od.Subtotal), 0) 
            FROM order_details od 
            WHERE od.OrderID = o.OrderID
        ) WHERE o.OrderID = $order_id
    ");
}


// --- ROUTING ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    send_error('No action specified.');
}

// Use a transaction for all POST actions to ensure data integrity
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
}

try {
    switch ($action) {
        // --- SESSION & READ-ONLY ACTIONS (GET) ---
        case 'getPosSession':
            $order_id = $_SESSION['pos_order_id'] ?? null;
            send_success(['order_id' => $order_id]);
            break;

        case 'clearPosSession':
            unset($_SESSION['pos_order_id']);
            send_success(['message' => 'Session cleared.']);
            break;

        // --- READ-ONLY ACTIONS (GET) ---
        case 'getInitialData':
            $response_data = [];
            $tables_result = $conn->query("SELECT TableID, TableNumber FROM restaurant_tables ORDER BY TableNumber");
            $response_data['tables'] = $tables_result->fetch_all(MYSQLI_ASSOC);
            
            $active_orders_result = $conn->query("SELECT OrderID, TableID FROM orders WHERE OrderStatus = 'Pending'");
            $active_orders = [];
            while($row = $active_orders_result->fetch_assoc()) {
                $active_orders[$row['TableID']] = $row;
            }
            $response_data['active_orders'] = $active_orders;

            $category_result = $conn->query("SELECT CategoryID, CategoryName FROM menu_categories ORDER BY CategoryName");
            $response_data['categories'] = $category_result->fetch_all(MYSQLI_ASSOC);
            
            $menu_items_result = $conn->query("SELECT MenuItemID, CategoryID, Name, Price, ImageUrl FROM menu_items WHERE IsAvailable = TRUE ORDER BY Name");
            $response_data['menu_items'] = $menu_items_result->fetch_all(MYSQLI_ASSOC);

            $staff_result = $conn->query("SELECT StaffID, FirstName, LastName FROM staff WHERE IsActive = TRUE ORDER BY FirstName");
            $response_data['staff'] = $staff_result->fetch_all(MYSQLI_ASSOC);

            send_success(['data' => $response_data]);
            break;

        case 'getOrderDetails':
            $order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
            if (!$order_id) send_error('Invalid Order ID.');
            
            $data = get_order_details($conn, $order_id);
            if (!$data) send_error('Order not found.', 404);

            send_success(['data' => $data]);
            break;

        // --- WRITE ACTIONS (POST) ---
        case 'setPosSession':
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            if (!$order_id) send_error('Invalid Order ID for session.');
            
            $_SESSION['pos_order_id'] = $order_id;
            send_success(['message' => 'Session set.']);
            break;

        case 'createOrder':
            $table_id = filter_input(INPUT_POST, 'table_id', FILTER_VALIDATE_INT);
            $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);

            if (!$table_id || !$staff_id) send_error('Table ID and Staff ID are required.');

            $stmt_check = $conn->prepare("SELECT OrderID FROM orders WHERE TableID = ? AND OrderStatus = 'Pending'");
            $stmt_check->bind_param("i", $table_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                send_error("Table #$table_id already has an active order.", 409);
            }
            $stmt_check->close();

            $stmt = $conn->prepare("INSERT INTO orders (TableID, StaffID, OrderStatus) VALUES (?, ?, 'Pending')");
            $stmt->bind_param("ii", $table_id, $staff_id);
            if (!$stmt->execute()) throw new Exception("Failed to create order: " . $stmt->error);
            
            $new_order_id = $stmt->insert_id;
            $stmt->close();
            
            $conn->commit();
            send_success(['order_id' => $new_order_id, 'message' => "Order #$new_order_id created!"]);
            break;

        case 'addItemToOrder':
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $menu_item_id = filter_input(INPUT_POST, 'menu_item_id', FILTER_VALIDATE_INT);
            
            if (!$order_id || !$menu_item_id) send_error('Invalid order or item ID.');

            $stmt_item = $conn->prepare("SELECT Price, Name FROM menu_items WHERE MenuItemID = ? AND IsAvailable = TRUE");
            $stmt_item->bind_param("i", $menu_item_id);
            $stmt_item->execute();
            $item_result = $stmt_item->get_result();
            if ($item_result->num_rows === 0) send_error('Menu item not found or is unavailable.');
            $item_data = $item_result->fetch_assoc();
            $price = $item_data['Price'];
            $item_name = $item_data['Name']; // <-- BUG FIX: Capture item name
            $stmt_item->close();

            $stmt_check = $conn->prepare("SELECT OrderDetailID, Quantity FROM order_details WHERE OrderID = ? AND MenuItemID = ?");
            $stmt_check->bind_param("ii", $order_id, $menu_item_id);
            $stmt_check->execute();
            $existing_item = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();

            if ($existing_item) {
                $new_quantity = $existing_item['Quantity'] + 1;
                $new_subtotal = $price * $new_quantity;
                $stmt_update = $conn->prepare("UPDATE order_details SET Quantity = ?, Subtotal = ? WHERE OrderDetailID = ?");
                $stmt_update->bind_param("idi", $new_quantity, $new_subtotal, $existing_item['OrderDetailID']);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                $subtotal = $price * 1;
                $stmt_insert = $conn->prepare("INSERT INTO order_details (OrderID, MenuItemID, Quantity, Subtotal) VALUES (?, ?, 1, ?)");
                $stmt_insert->bind_param("iid", $order_id, $menu_item_id, $subtotal);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
            
            update_order_total($conn, $order_id);
            
            $conn->commit();
            
            // BUG FIX: Add the item name to the response payload for the toast message
            $response_data = get_order_details($conn, $order_id);
            $response_data['item_name'] = $item_name; 
            send_success(['data' => $response_data]);
            break;

        case 'updateItemQuantity':
            $order_detail_id = filter_input(INPUT_POST, 'order_detail_id', FILTER_VALIDATE_INT);
            $new_quantity = filter_input(INPUT_POST, 'new_quantity', FILTER_VALIDATE_INT);

            if (!$order_detail_id || !isset($new_quantity) || $new_quantity < 0) {
                send_error('Invalid data for quantity update.');
            }

            $stmt_details = $conn->prepare("SELECT od.OrderID, mi.Price FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID WHERE od.OrderDetailID = ?");
            $stmt_details->bind_param("i", $order_detail_id);
            $stmt_details->execute();
            $item_details = $stmt_details->get_result()->fetch_assoc();
            if (!$item_details) throw new Exception('Order item not found.');
            $order_id = $item_details['OrderID'];
            $price = $item_details['Price'];
            $stmt_details->close();

            if ($new_quantity == 0) {
                $stmt_delete = $conn->prepare("DELETE FROM order_details WHERE OrderDetailID = ?");
                $stmt_delete->bind_param("i", $order_detail_id);
                $stmt_delete->execute();
                $stmt_delete->close();
            } else {
                $new_subtotal = $price * $new_quantity;
                $stmt_update = $conn->prepare("UPDATE order_details SET Quantity = ?, Subtotal = ? WHERE OrderDetailID = ?");
                $stmt_update->bind_param("idi", $new_quantity, $new_subtotal, $order_detail_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
            
            update_order_total($conn, $order_id);
            
            $conn->commit();
            send_success(['data' => get_order_details($conn, $order_id)]);
            break;

        case 'updateOrderStatus':
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            $valid_statuses = ['Completed', 'Cancelled'];

            if (!$order_id || !in_array($status, $valid_statuses)) {
                send_error('Invalid Order ID or status provided.');
            }

            $stmt = $conn->prepare("UPDATE orders SET OrderStatus = ? WHERE OrderID = ? AND OrderStatus = 'Pending'");
            $stmt->bind_param("si", $status, $order_id);
            if (!$stmt->execute()) throw new Exception("Failed to update order status: " . $stmt->error);
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Order #$order_id not found or already processed.");
            }

            $stmt->close();
            $conn->commit();
            send_success(['message' => "Order #{$order_id} status updated to {$status}."]);
            break;

        default:
            send_error('Invalid action specified.');
            break;
    }
} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    error_log("POS System Error: " . $e->getMessage());
    send_error('A server error occurred. Please try again.', 500);
}

$conn->close();
