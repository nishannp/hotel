<?php
// ajax/ajax_handler_orders.php
require_once '../config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'fetchAll'; // Default action for backward compatibility if needed
$response = ['success' => false, 'data' => []];

try {
    switch ($action) {
        case 'fetchActiveOrders':
            $sql = "SELECT o.OrderID, o.OrderTime, o.OrderStatus, t.TableNumber, s.FirstName 
                    FROM orders o
                    JOIN restaurant_tables t ON o.TableID = t.TableID
                    JOIN staff s ON o.StaffID = s.StaffID
                    WHERE o.OrderStatus IN ('Pending', 'In-Progress')
                    ORDER BY o.OrderTime ASC";
            $result = $conn->query($sql);
            $active_orders = [];
            while ($row = $result->fetch_assoc()) {
                $active_orders[] = $row;
            }
            $response = ['success' => true, 'data' => $active_orders];
            break;

        case 'fetchOrderHistory':
            // --- Pagination & Filtering Logic ---
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10; // Items per page
            $offset = ($page - 1) * $limit;

            // Build WHERE clause based on filters
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
                $where_clauses[] = '(o.OrderID LIKE ? OR t.TableNumber LIKE ? OR s.FirstName LIKE ? OR s.LastName LIKE ?)';
                $params = array_merge($params, [$term, $term, $term, $term]);
                $types .= 'ssss';
            }
            if (!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
                $where_clauses[] = 'o.OrderTime BETWEEN ? AND ?';
                $params[] = $_GET['startDate'] . ' 00:00:00';
                $params[] = $_GET['endDate'] . ' 23:59:59';
                $types .= 'ss';
            }

            $where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);

            // Get total count for pagination
            $count_sql = "SELECT COUNT(o.OrderID) as total FROM orders o JOIN restaurant_tables t ON o.TableID = t.TableID JOIN staff s ON o.StaffID = s.StaffID $where_sql";
            $stmt_count = $conn->prepare($count_sql);
            if (!empty($params)) $stmt_count->bind_param($types, ...$params);
            $stmt_count->execute();
            $total_results = $stmt_count->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total_results / $limit);
            $stmt_count->close();

            // Get paginated order data
            $sql_orders = "SELECT o.OrderID, o.OrderTime, o.OrderStatus, o.TotalAmount, t.TableNumber, s.FirstName, s.LastName, c.FirstName AS CustomerFirstName, c.LastName AS CustomerLastName
                           FROM orders o
                           JOIN restaurant_tables t ON o.TableID = t.TableID
                           JOIN staff s ON o.StaffID = s.StaffID
                           LEFT JOIN customers c ON o.CustomerID = c.CustomerID
                           $where_sql
                           ORDER BY o.OrderID DESC
                           LIMIT ? OFFSET ?";
            
            $stmt_orders = $conn->prepare($sql_orders);
            $params_with_pagination = $params;
            $params_with_pagination[] = $limit;
            $params_with_pagination[] = $offset;
            $types_with_pagination = $types . 'ii';
            if (!empty($params)) $stmt_orders->bind_param($types_with_pagination, ...$params_with_pagination);
            else $stmt_orders->bind_param('ii', $limit, $offset);
            
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
                        $detail_row['ItemImageUrl'] = UPLOADS_URL . $detail_row['ItemImageUrl'];
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