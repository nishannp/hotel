<?php
// ajax/ajax_handler_orders.php - Updated for Party System
require_once '../config.php';
header('Content-Type: application/json');

// Determine action from GET or POST
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetchAll';
$response = ['success' => false, 'data' => []];

try {
    switch ($action) {
        case 'deleteAllOrders':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method.');
            }
            $conn->begin_transaction();
            try {
                // It's safer to disable foreign key checks temporarily
                $conn->query("SET FOREIGN_KEY_CHECKS=0");
                
                // List of tables to truncate
                $tables = ["payments", "order_details", "orders", "customer_parties"];
                foreach ($tables as $table) {
                    if ($conn->query("TRUNCATE TABLE `$table`") === FALSE) {
                        throw new Exception("Failed to truncate table: $table");
                    }
                }
                
                // Re-enable foreign key checks
                $conn->query("SET FOREIGN_KEY_CHECKS=1");
                
                $conn->commit();
                $response = ['success' => true, 'message' => 'All order data has been successfully deleted.'];
            } catch (Exception $e) {
                $conn->rollback();
                $conn->query("SET FOREIGN_KEY_CHECKS=1"); // Ensure it's re-enabled on failure
                throw $e;
            }
            break;

        case 'deleteOrder':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method.');
            }
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            if (!$order_id) {
                throw new Exception('Invalid Order ID.');
            }

            $conn->begin_transaction();

            try {
                // Get PartyID before deleting the order
                $stmt_party = $conn->prepare("SELECT PartyID FROM orders WHERE OrderID = ?");
                $stmt_party->bind_param("i", $order_id);
                $stmt_party->execute();
                $party_result = $stmt_party->get_result();
                $party_id = ($party_result->num_rows > 0) ? $party_result->fetch_assoc()['PartyID'] : null;
                $stmt_party->close();

                // 1. Delete associated payments first to avoid foreign key constraints
                $stmt_delete_payments = $conn->prepare("DELETE FROM payments WHERE OrderID = ?");
                $stmt_delete_payments->bind_param("i", $order_id);
                $stmt_delete_payments->execute();
                $stmt_delete_payments->close();

                // 2. Delete the order (order_details will be cascade deleted)
                $stmt_delete_order = $conn->prepare("DELETE FROM orders WHERE OrderID = ?");
                $stmt_delete_order->bind_param("i", $order_id);
                $stmt_delete_order->execute();
                $deleted_rows = $stmt_delete_order->affected_rows;
                $stmt_delete_order->close();

                if ($deleted_rows === 0) {
                    throw new Exception("Order not found or already deleted.");
                }

                // 3. If a party was associated, check if it has other orders. If not, delete it.
                if ($party_id) {
                    $stmt_check = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE PartyID = ?");
                    $stmt_check->bind_param("i", $party_id);
                    $stmt_check->execute();
                    $order_count = $stmt_check->get_result()->fetch_assoc()['order_count'];
                    $stmt_check->close();

                    if ($order_count == 0) {
                        $stmt_delete_party = $conn->prepare("DELETE FROM customer_parties WHERE PartyID = ?");
                        $stmt_delete_party->bind_param("i", $party_id);
                        $stmt_delete_party->execute();
                        $stmt_delete_party->close();
                    }
                }

                $conn->commit();
                $response = ['success' => true, 'message' => 'Order deleted successfully.'];

            } catch (Exception $e) {
                $conn->rollback();
                throw $e; // Re-throw to be caught by the outer catch block
            }
            break;

        case 'fetchActiveOrders':
            $sql = "SELECT o.OrderID, o.OrderTime, o.OrderStatus, t.TableNumber, cp.PartyIdentifier, s.FirstName 
                    FROM orders o
                    JOIN customer_parties cp ON o.PartyID = cp.PartyID
                    JOIN restaurant_tables t ON cp.TableID = t.TableID
                    JOIN staff s ON o.StaffID = s.StaffID
                    WHERE o.OrderStatus IN ('Pending', 'In-Progress')
                    ORDER BY o.OrderTime ASC";
            $result = $conn->query($sql);
            $active_orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $response = ['success' => true, 'data' => $active_orders];
            break;

        case 'fetchOrderHistory':
            // --- Pagination & Filtering Logic ---
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Build WHERE clause
            $where_clauses = [];
            $params = [];
            $types = '';

            if (!empty($_GET['status']) && $_GET['status'] !== 'All') {
                $where_clauses[] = 'o.OrderStatus = ?';
                $params[] = $_GET['status'];
                $types .= 's';
            }
            if (!empty($_GET['searchTerm'])) {
                $term = '%' . $_GET['searchTerm'] . '%';
                $where_clauses[] = '(o.OrderID LIKE ? OR t.TableNumber LIKE ? OR cp.PartyIdentifier LIKE ? OR s.FirstName LIKE ? OR s.LastName LIKE ?)';
                $params = array_merge($params, [$term, $term, $term, $term, $term]);
                $types .= 'sssss';
            }
            if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
                $where_clauses[] = 'o.OrderTime BETWEEN ? AND ?';
                $params[] = $_GET['startDate'] . ' 00:00:00';
                $params[] = $_GET['endDate'] . ' 23:59:59';
                $types .= 'ss';
            }

            $base_query = "FROM orders o
                           JOIN customer_parties cp ON o.PartyID = cp.PartyID
                           JOIN restaurant_tables t ON cp.TableID = t.TableID
                           JOIN staff s ON o.StaffID = s.StaffID
                           LEFT JOIN customers c ON o.CustomerID = c.CustomerID";
            $where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);

            // Get total count
            $count_sql = "SELECT COUNT(o.OrderID) as total $base_query $where_sql";
            $stmt_count = $conn->prepare($count_sql);
            if (!empty($params)) $stmt_count->bind_param($types, ...$params);
            $stmt_count->execute();
            $total_results = $stmt_count->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total_results / $limit);
            $stmt_count->close();

            // Get paginated order data
            $sql_orders = "SELECT o.OrderID, o.OrderTime, o.OrderStatus, o.TotalAmount, t.TableNumber, cp.PartyIdentifier, s.FirstName, s.LastName, c.FirstName AS CustomerFirstName, c.LastName AS CustomerLastName
                           $base_query
                           $where_sql
                           ORDER BY o.OrderID DESC
                           LIMIT ? OFFSET ?";
            
            $stmt_orders = $conn->prepare($sql_orders);
            $params_with_pagination = array_merge($params, [$limit, $offset]);
            $types_with_pagination = $types . 'ii';
            $stmt_orders->bind_param($types_with_pagination, ...$params_with_pagination);
            
            $stmt_orders->execute();
            $orders_result = $stmt_orders->get_result();
            
            $orders = [];
            $order_ids = [];
            while ($row = $orders_result->fetch_assoc()) {
                $orders[$row['OrderID']] = $row;
                $orders[$row['OrderID']]['Details'] = [];
                $order_ids[] = $row['OrderID'];
            }
            $stmt_orders->close();

            // Fetch details for the paginated orders
            if (!empty($order_ids)) {
                $ids_string = implode(',', $order_ids);
                $sql_details = "SELECT od.OrderID, od.Quantity, od.Subtotal, mi.Name AS ItemName, mi.ImageUrl AS ItemImageUrl
                                FROM order_details od
                                JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
                                WHERE od.OrderID IN ($ids_string)";
                $details_result = $conn->query($sql_details);
                while ($detail_row = $details_result->fetch_assoc()) {
                    if (!empty($detail_row['ItemImageUrl'])) {
                        $detail_row['ItemImageUrl'] = UPLOADS_URL . 'menu_items/' . $detail_row['ItemImageUrl'];
                    }
                    $orders[$detail_row['OrderID']]['Details'][] = $detail_row;
                }
            }

            $response = [
                'success' => true, 
                'data' => array_values($orders),
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $total_pages,
                    'totalResults' => $total_results
                ]
            ];
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
