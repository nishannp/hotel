<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DEVELOPMENT FLAG ---
// Set to true for detailed error messages, false for production
define('DEVELOPMENT_MODE', true);

/**
 * ajax/ajax_handler_payments.php
 * Handles payment processing and fetching of unpaid orders for the POS system.
 * @version 2.0 - Refactored for security, consistency, and enhanced debugging.
 */

// --- INITIALIZATION ---
session_start();
header('Content-Type: application/json');
// Ensure this path is correct for your project structure
require_once '../config.php'; 

// --- UTILITY FUNCTIONS (Copied from main handler for consistency) ---

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
 * @param array $debug_info Optional debugging information for development mode.
 */
function send_error(string $message, int $code = 400, array $debug_info = []): void {
    http_response_code($code);
    $response = ['success' => false, 'message' => $message];
    if (DEVELOPMENT_MODE && !empty($debug_info)) {
        $response['debug'] = $debug_info;
    }
    echo json_encode($response);
    exit;
}

// --- ROUTING ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    send_error('No action specified.');
}

try {
    switch ($action) {
        case 'getUnpaidOrders':
            // Fetch main order details for completed, unpaid orders.
            // This is safer and more scalable than using GROUP_CONCAT.
            $sql_orders = "SELECT 
                                o.OrderID, 
                                o.TotalAmount, 
                                o.OrderTime, 
                                rt.TableNumber,
                                cp.PartyIdentifier
                           FROM orders o
                           JOIN customer_parties cp ON o.PartyID = cp.PartyID
                           JOIN restaurant_tables rt ON cp.TableID = rt.TableID
                           LEFT JOIN payments p ON o.OrderID = p.OrderID
                           WHERE o.OrderStatus = 'Completed' AND p.PaymentID IS NULL
                           ORDER BY o.OrderTime DESC";
            
            $result_orders = $conn->query($sql_orders);
            if (!$result_orders) throw new Exception("Failed to fetch unpaid orders: " . $conn->error);
            
            $orders = $result_orders->fetch_all(MYSQLI_ASSOC);

            // Prepare a statement to fetch items for each order.
            $stmt_items = $conn->prepare("
                SELECT mi.Name as name, od.Quantity as quantity, od.Subtotal as subtotal
                FROM order_details od 
                JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID 
                WHERE od.OrderID = ?
            ");

            // Loop through each order and attach its items.
            foreach ($orders as &$order) {
                $stmt_items->bind_param("i", $order['OrderID']);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();
                $order['items'] = $result_items->fetch_all(MYSQLI_ASSOC);
            }
            unset($order); // Unset reference to the last element
            $stmt_items->close();

            send_success(['data' => $orders]);
            break;

        case 'processPayment':
            // Sanitize all inputs for security and consistency.
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $amount_paid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_SPECIAL_CHARS);
            $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
            $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_SANITIZE_SPECIAL_CHARS);
            $transaction_id = !empty($transaction_id) ? $transaction_id : null;

            if (!$order_id || !isset($amount_paid) || !$payment_method) {
                send_error('All required fields must be filled correctly.');
            }

            $conn->begin_transaction();

            // Determine the staff ID to process the payment.
            // Since only admins can log in, we'll assign this to a default manager/cashier.
            $staff_id_to_process = null;
            $staff_query = "SELECT StaffID FROM staff WHERE Role IN ('Manager', 'Cashier') AND IsActive = TRUE ORDER BY StaffID LIMIT 1";
            $staff_result = $conn->query($staff_query);
            if ($staff_result && $staff_result->num_rows > 0) {
                $staff_id_to_process = $staff_result->fetch_assoc()['StaffID'];
            } else {
                // Fallback: if no manager/cashier, use the very first active staff member
                $fallback_query = "SELECT StaffID FROM staff WHERE IsActive = TRUE ORDER BY StaffID LIMIT 1";
                $fallback_result = $conn->query($fallback_query);
                if ($fallback_result && $fallback_result->num_rows > 0) {
                    $staff_id_to_process = $fallback_result->fetch_assoc()['StaffID'];
                } else {
                    throw new Exception("No active staff available to process the payment.");
                }
            }

            // 1. Update the final TotalAmount in the orders table.
            $stmt_update = $conn->prepare("UPDATE orders SET TotalAmount = ? WHERE OrderID = ?");
            $stmt_update->bind_param("di", $amount_paid, $order_id);
            if (!$stmt_update->execute()) throw new Exception("Failed to update order total: " . $stmt_update->error);
            $stmt_update->close();

            // 2. Insert the payment record.
            $stmt_insert = $conn->prepare("INSERT INTO payments (OrderID, AmountPaid, PaymentMethod, ProcessedByStaffID, TransactionID) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("idsis", $order_id, $amount_paid, $payment_method, $staff_id_to_process, $transaction_id);
            if (!$stmt_insert->execute()) throw new Exception("Failed to insert payment record: " . $stmt_insert->error);
            $stmt_insert->close();

            // 3. Update the party status to 'Departed'.
            $stmt_party = $conn->prepare("UPDATE customer_parties SET PartyStatus = 'Departed' WHERE PartyID = (SELECT PartyID FROM orders WHERE OrderID = ?)");
            $stmt_party->bind_param("i", $order_id);
            if (!$stmt_party->execute()) throw new Exception("Failed to update party status: " . $stmt_party->error);
            $stmt_party->close();
            
            $conn->commit();
            send_success(['message' => 'Payment Successful!']);
            break;

        default:
            send_error('Invalid action specified.');
            break;
    }
} catch (Exception $e) {
    if ($conn->ping()) { // Check if connection is still alive before rollback
        $conn->rollback();
    }
    
    error_log("Payment Handler Error: " . $e->getMessage());

    // Send detailed error if in development mode
    $debug_info = [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    send_error('A server error occurred during payment processing.', 500, $debug_info);
}

$conn->close();
