<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once '../config.php';

function send_success(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function send_error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

if (empty($start_date) || empty($end_date)) {
    send_error('Start and end dates are required.');
}

$response = [];

try {
    // --- KPIs ---
    $hotel_revenue_stmt = $conn->prepare("SELECT COALESCE(SUM(TotalAmount), 0) as total FROM orders WHERE OrderStatus = 'Completed' AND DATE(OrderTime) BETWEEN ? AND ?");
    $hotel_revenue_stmt->bind_param("ss", $start_date, $end_date);
    $hotel_revenue_stmt->execute();
    $hotel_revenue = $hotel_revenue_stmt->get_result()->fetch_assoc()['total'];
    $response['kpi']['hotel_revenue'] = $hotel_revenue;

    $store_revenue_stmt = $conn->prepare("SELECT COALESCE(SUM(TotalAmount), 0) as total FROM store_sales_log WHERE DATE(SaleTime) BETWEEN ? AND ?");
    $store_revenue_stmt->bind_param("ss", $start_date, $end_date);
    $store_revenue_stmt->execute();
    $store_revenue = $store_revenue_stmt->get_result()->fetch_assoc()['total'];
    $response['kpi']['store_revenue'] = $store_revenue;

    $expenses_stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date BETWEEN ? AND ?");
    $expenses_stmt->bind_param("ss", $start_date, $end_date);
    $expenses_stmt->execute();
    $total_expenses = $expenses_stmt->get_result()->fetch_assoc()['total'];
    $response['kpi']['total_expenses'] = $total_expenses;

    $response['kpi']['total_revenue'] = $hotel_revenue + $store_revenue;
    $response['kpi']['net_profit'] = $response['kpi']['total_revenue'] - $total_expenses;

    $orders_stmt = $conn->prepare("SELECT COUNT(OrderID) as count, COALESCE(AVG(TotalAmount), 0) as avg_order FROM orders WHERE OrderStatus = 'Completed' AND DATE(OrderTime) BETWEEN ? AND ?");
    $orders_stmt->bind_param("ss", $start_date, $end_date);
    $orders_stmt->execute();
    $order_stats = $orders_stmt->get_result()->fetch_assoc();
    $response['kpi']['hotel_orders'] = $order_stats['count'];
    $response['kpi']['avg_order_value'] = $order_stats['avg_order'];

    $hotel_items_stmt = $conn->prepare("SELECT COALESCE(SUM(od.Quantity), 0) as total FROM order_details od JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderStatus = 'Completed' AND DATE(o.OrderTime) BETWEEN ? AND ?");
    $hotel_items_stmt->bind_param("ss", $start_date, $end_date);
    $hotel_items_stmt->execute();
    $hotel_items = $hotel_items_stmt->get_result()->fetch_assoc()['total'];

    $store_items_stmt = $conn->prepare("SELECT COALESCE(SUM(Quantity), 0) as total FROM store_sales_log WHERE DATE(SaleTime) BETWEEN ? AND ?");
    $store_items_stmt->bind_param("ss", $start_date, $end_date);
    $store_items_stmt->execute();
    $store_items = $store_items_stmt->get_result()->fetch_assoc()['total'];
    $response['kpi']['total_items_sold'] = $hotel_items + $store_items;

    // --- CHARTS & LISTS ---
    $rev_exp_query = "
        SELECT
            d.date,
            COALESCE(h.total_hotel_revenue, 0) + COALESCE(s.total_store_revenue, 0) as daily_revenue,
            COALESCE(e.total_expenses, 0) as daily_expenses
        FROM
            (SELECT ? + INTERVAL (t.i) DAY as date
             FROM (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30) t
             WHERE ? + INTERVAL (t.i) DAY <= ?) d
        LEFT JOIN (
            SELECT DATE(OrderTime) as date, SUM(TotalAmount) as total_hotel_revenue
            FROM orders WHERE OrderStatus = 'Completed' AND DATE(OrderTime) BETWEEN ? AND ? GROUP BY DATE(OrderTime)
        ) h ON d.date = h.date
        LEFT JOIN (
            SELECT DATE(SaleTime) as date, SUM(TotalAmount) as total_store_revenue
            FROM store_sales_log WHERE DATE(SaleTime) BETWEEN ? AND ? GROUP BY DATE(SaleTime)
        ) s ON d.date = s.date
        LEFT JOIN (
            SELECT expense_date as date, SUM(amount) as total_expenses
            FROM expenses WHERE expense_date BETWEEN ? AND ? GROUP BY expense_date
        ) e ON d.date = e.date
        ORDER BY d.date;
    ";
    $rev_exp_stmt = $conn->prepare($rev_exp_query);
    $rev_exp_stmt->bind_param("sssssssss", $start_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $rev_exp_stmt->execute();
    $response['charts']['revenue_vs_expenses'] = $rev_exp_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $exp_cat_stmt = $conn->prepare("SELECT ec.name, SUM(e.amount) as total FROM expenses e JOIN expense_categories ec ON e.category_id = ec.id WHERE e.expense_date BETWEEN ? AND ? GROUP BY ec.name ORDER BY total DESC");
    $exp_cat_stmt->bind_param("ss", $start_date, $end_date);
    $exp_cat_stmt->execute();
    $response['charts']['expenses_by_category'] = $exp_cat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $payment_methods_stmt = $conn->prepare("SELECT PaymentMethod, SUM(AmountPaid) as total FROM payments WHERE DATE(PaymentTime) BETWEEN ? AND ? GROUP BY PaymentMethod ORDER BY total DESC");
    $payment_methods_stmt->bind_param("ss", $start_date, $end_date);
    $payment_methods_stmt->execute();
    $response['charts']['payment_methods'] = $payment_methods_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $top_staff_stmt = $conn->prepare("SELECT s.FirstName, s.LastName, SUM(o.TotalAmount) as total_sales FROM orders o JOIN staff s ON o.StaffID = s.StaffID WHERE o.OrderStatus = 'Completed' AND DATE(o.OrderTime) BETWEEN ? AND ? GROUP BY o.StaffID ORDER BY total_sales DESC LIMIT 3");
    $top_staff_stmt->bind_param("ss", $start_date, $end_date);
    $top_staff_stmt->execute();
    $response['lists']['top_staff'] = $top_staff_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    send_success(['data' => $response]);

} catch (Exception $e) {
    send_error('An error occurred while fetching analytics data: ' . $e->getMessage(), 500);
}

$conn->close();
