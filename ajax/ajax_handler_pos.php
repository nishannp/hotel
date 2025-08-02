<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * ajax/ajax_handler_pos.php
 * Refactored backend handler for the Point of Sale (POS) system with party management.
 * Handles all asynchronous requests from the POS interface, supporting multiple parties per table.
 * @version 5.1 - Patched for PHP 8+ compatibility.
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
 * Fetches complete details for a specific order, including items, party, and table info.
 * @param mysqli $conn The database connection.
 * @param int $order_id The ID of the order to fetch.
 * @return array|null The order details or null if not found.
 */
function get_order_details(mysqli $conn, int $order_id): ?array {
    $details = [];

    // Fetch main order info along with party and table details
    $stmt_order = $conn->prepare("
        SELECT o.OrderID, o.StaffID, o.OrderTime, o.OrderStatus, o.TotalAmount,
               cp.PartyID, cp.PartyIdentifier,
               rt.TableID, rt.TableNumber
        FROM orders o
        JOIN customer_parties cp ON o.PartyID = cp.PartyID
        JOIN restaurant_tables rt ON cp.TableID = rt.TableID
        WHERE o.OrderID = ?
    ");
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
            
            // 1. Fetch all tables with their status
            $tables_result = $conn->query("SELECT TableID, TableNumber, Status FROM restaurant_tables ORDER BY TableNumber");
            $tables = $tables_result->fetch_all(MYSQLI_ASSOC);

            // 2. Fetch all active parties and their pending orders
            $parties_result = $conn->query("
                SELECT cp.PartyID, cp.TableID, cp.PartyIdentifier, o.OrderID
                FROM customer_parties cp
                JOIN orders o ON cp.PartyID = o.PartyID
                WHERE cp.PartyStatus != 'Departed' AND o.OrderStatus = 'Pending'
            ");
            $active_parties = $parties_result->fetch_all(MYSQLI_ASSOC);

            // 3. Structure data for the frontend
            $tables_data = [];
            foreach ($tables as $table) {
                $tables_data[$table['TableID']] = $table;
                $tables_data[$table['TableID']]['parties'] = [];
            }
            foreach ($active_parties as $party) {
                if (isset($tables_data[$party['TableID']])) {
                    $tables_data[$party['TableID']]['parties'][] = $party;
                }
            }
            $response_data['tables_data'] = array_values($tables_data); // Convert to array for JS

            // 4. Fetch other necessary data
            $category_result = $conn->query("SELECT CategoryID, CategoryName FROM menu_categories ORDER BY CategoryName");
            $response_data['categories'] = $category_result->fetch_all(MYSQLI_ASSOC);
            
            $menu_items_result = $conn->query("SELECT MenuItemID, CategoryID, Name, Price, ImageUrl FROM menu_items WHERE IsAvailable = TRUE ORDER BY Name");
            $response_data['menu_items'] = $menu_items_result->fetch_all(MYSQLI_ASSOC);

            $staff_result = $conn->query("SELECT StaffID, FirstName, LastName FROM staff WHERE IsActive = TRUE ORDER BY FirstName");
            $response_data['staff'] = $staff_result->fetch_all(MYSQLI_ASSOC);

            send_success(['data' => $response_data]);
            break;

        case 'getPartiesForTable':
            $table_id = filter_input(INPUT_GET, 'table_id', FILTER_VALIDATE_INT);
            if (!$table_id) send_error('Invalid Table ID.');

            $stmt = $conn->prepare("
                SELECT cp.PartyID, cp.PartyIdentifier, o.OrderID
                FROM customer_parties cp
                JOIN orders o ON cp.PartyID = o.PartyID
                WHERE cp.TableID = ? AND cp.PartyStatus != 'Departed' AND o.OrderStatus = 'Pending'
                ORDER BY cp.SeatingTime
            ");
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $parties = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            send_success(['data' => $parties]);
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

        case 'createPartyAndOrder':
            $table_id = filter_input(INPUT_POST, 'table_id', FILTER_VALIDATE_INT);
            $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);

            if (!$table_id || !$staff_id) send_error('Table ID and Staff ID are required.');

            // Determine party identifier (e.g., Party A, Party B)
            $stmt_count = $conn->prepare("SELECT COUNT(*) FROM customer_parties WHERE TableID = ?");
            $stmt_count->bind_param("i", $table_id);
            $stmt_count->execute();
            $count = $stmt_count->get_result()->fetch_row()[0];
            $party_identifier = 'Party ' . chr(65 + $count);
            $stmt_count->close();

            // Create the new party
            $num_guests = rand(1, 6); // Automatic number of guests
            $stmt_party = $conn->prepare("INSERT INTO customer_parties (TableID, NumberOfGuests, PartyIdentifier, PartyStatus) VALUES (?, ?, ?, 'Ordering')");
            $stmt_party->bind_param("iis", $table_id, $num_guests, $party_identifier);
            if (!$stmt_party->execute()) throw new Exception("Failed to create party: " . $stmt_party->error);
            $new_party_id = $stmt_party->insert_id;
            $stmt_party->close();

            // Create an order for the new party
            $stmt_order = $conn->prepare("INSERT INTO orders (PartyID, StaffID, OrderStatus) VALUES (?, ?, 'Pending')");
            $stmt_order->bind_param("ii", $new_party_id, $staff_id);
            if (!$stmt_order->execute()) throw new Exception("Failed to create order: " . $stmt_order->error);
            $new_order_id = $stmt_order->insert_id;
            $stmt_order->close();
            
            $conn->commit();
            send_success([
                'order_id' => $new_order_id, 
                'party_id' => $new_party_id,
                'message' => "{$party_identifier} created for table!"
            ]);
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
            $item_name = $item_data['Name'];
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
            // FIX: Replace deprecated FILTER_SANITIZE_STRING
            $raw_status = $_POST['status'] ?? '';
            $status = htmlspecialchars($raw_status, ENT_QUOTES, 'UTF-8');
            
            $valid_statuses = ['Completed', 'Cancelled'];

            if (!$order_id || !in_array($status, $valid_statuses)) {
                send_error('Invalid Order ID or status provided.');
            }

            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET OrderStatus = ? WHERE OrderID = ? AND OrderStatus = 'Pending'");
            $stmt->bind_param("si", $status, $order_id);
            if (!$stmt->execute()) throw new Exception("Failed to update order status: " . $stmt->error);
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Order #$order_id not found or already processed.");
            }
            $stmt->close();

            // Update corresponding party status
            $party_status = ($status === 'Completed') ? 'Billing' : 'Departed';
            $stmt_party = $conn->prepare("
                UPDATE customer_parties SET PartyStatus = ? 
                WHERE PartyID = (SELECT PartyID FROM orders WHERE OrderID = ?)
            ");
            $stmt_party->bind_param("si", $party_status, $order_id);
            if (!$stmt_party->execute()) throw new Exception("Failed to update party status: " . $stmt_party->error);
            $stmt_party->close();

            $conn->commit();
            send_success(['message' => "Order #{$order_id} status updated to {$status}."]);
            break;

        default:
            send_error('Invalid action specified.');
            break;
    }
} catch (Exception $e) {
    // FIX: Removed the check for $conn->in_transaction as it's not available in all PHP versions.
    // Calling rollback() is safe even if a transaction is not active.
    $conn->rollback();
    
    error_log("POS System Error: " . $e->getMessage());
    send_error('A server error occurred. Please try again.', 500);
}

$conn->close();
