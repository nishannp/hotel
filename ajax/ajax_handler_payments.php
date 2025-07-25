<?php
// ajax/ajax_handler_payments.php - Enhanced to include order items
require_once '../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'getUnpaidOrders':
        // This query is now more complex. It fetches all completed, unpaid orders,
        // and for each order, it uses GROUP_CONCAT to create a JSON-like string of its items.
        $sql = "SELECT 
                    o.OrderID, 
                    o.TotalAmount, 
                    o.OrderTime, 
                    t.TableNumber,
                    (SELECT GROUP_CONCAT(
                        CONCAT(
                            '{\"name\":\"', REPLACE(mi.Name, '\"', '\\\"'), '\",',
                            '\"quantity\":', od.Quantity, ',',
                            '\"subtotal\":', od.Subtotal, '}'
                        )
                    ) 
                    FROM order_details od 
                    JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID 
                    WHERE od.OrderID = o.OrderID) as items_json
                FROM orders o
                JOIN restaurant_tables t ON o.TableID = t.TableID
                LEFT JOIN payments p ON o.OrderID = p.OrderID
                WHERE o.OrderStatus = 'Completed' AND p.PaymentID IS NULL
                ORDER BY o.OrderTime DESC";
        
        $result = $conn->query($sql);
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Decode the JSON string of items back into a PHP array
            $row['items'] = json_decode('[' . $row['items_json'] . ']', true);
            unset($row['items_json']); // Clean up the raw JSON string
            $orders[] = $row;
        }
        $response = ['success' => true, 'data' => $orders];
        break;

    case 'processPayment':
        // This case remains unchanged and is already correct.
        $order_id = $_POST['order_id'];
        $amount_paid = $_POST['amount_paid'];
        $payment_method = $_POST['payment_method'];
        $staff_id = $_POST['staff_id'];
        $transaction_id = !empty($_POST['transaction_id']) ? $_POST['transaction_id'] : null;

        if (empty($order_id) || empty($amount_paid) || empty($payment_method) || empty($staff_id)) {
            $response['message'] = 'All required fields must be filled.';
            break;
        }

        $sql_insert = "INSERT INTO payments (OrderID, AmountPaid, PaymentMethod, ProcessedByStaffID, TransactionID) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("idsis", $order_id, $amount_paid, $payment_method, $staff_id, $transaction_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Payment Successful!'];
        } else {
            $response['message'] = 'Error processing payment: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);