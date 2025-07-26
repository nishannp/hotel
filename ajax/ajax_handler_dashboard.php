<?php
// ajax/ajax_handler_dashboard.php
require_once '../config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

try {
    $data = [];
    $conn->begin_transaction();

    // =================================================================
    // 1. KPI Stats & Comparisons
    // =================================================================
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $yesterday_end = date('Y-m-d 23:59:59', strtotime('-1 day'));

    // Revenue
    $rev_today_res = $conn->query("SELECT SUM(TotalAmount) as total FROM orders WHERE OrderStatus = 'Completed' AND OrderTime BETWEEN '$today_start' AND '$today_end'");
    $data['kpi']['revenue_today'] = $rev_today_res->fetch_assoc()['total'] ?? 0;
    $rev_yesterday_res = $conn->query("SELECT SUM(TotalAmount) as total FROM orders WHERE OrderStatus = 'Completed' AND OrderTime BETWEEN '$yesterday_start' AND '$yesterday_end'");
    $data['kpi']['revenue_yesterday'] = $rev_yesterday_res->fetch_assoc()['total'] ?? 0;

    // Orders
    $orders_today_res = $conn->query("SELECT COUNT(OrderID) as count FROM orders WHERE OrderTime BETWEEN '$today_start' AND '$today_end'");
    $data['kpi']['orders_today'] = $orders_today_res->fetch_assoc()['count'] ?? 0;

    // =================================================================
    // 2. Table Status (for Pie Chart)
    // =================================================================
    $table_status_res = $conn->query("SELECT Status, COUNT(TableID) as count FROM restaurant_tables GROUP BY Status");
    $table_statuses = ['Available' => 0, 'Occupied' => 0, 'Reserved' => 0];
    while($row = $table_status_res->fetch_assoc()) {
        if (array_key_exists($row['Status'], $table_statuses)) {
            $table_statuses[$row['Status']] = $row['count'];
        }
    }
    $data['table_statuses'] = $table_statuses;

    // =================================================================
    // 3. Sales Chart Data (Last 30 Days)
    // =================================================================
    $sales_chart_data = ['labels' => [], 'values' => []];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $result = $conn->query("SELECT SUM(TotalAmount) as daily_total FROM orders WHERE OrderStatus = 'Completed' AND DATE(OrderTime) = '$date'");
        $sales_chart_data['labels'][] = date('M j', strtotime($date));
        $sales_chart_data['values'][] = $result->fetch_assoc()['daily_total'] ?? 0;
    }
    $data['sales_chart'] = $sales_chart_data;

    // =================================================================
    // 4. Menu & Inventory Insights
    // =================================================================
    $month_start = date('Y-m-01 00:00:00');
    
    // Best Sellers
    $best_sellers_res = $conn->query("SELECT mi.Name, COUNT(od.MenuItemID) as order_count FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderTime >= '$month_start' GROUP BY od.MenuItemID ORDER BY order_count DESC LIMIT 5");
    $data['menu_insights']['best_sellers'] = $best_sellers_res->fetch_all(MYSQLI_ASSOC);

    // Worst Sellers
    $worst_sellers_res = $conn->query("SELECT mi.Name, COUNT(od.MenuItemID) as order_count FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderTime >= '$month_start' GROUP BY od.MenuItemID ORDER BY order_count ASC LIMIT 5");
    $data['menu_insights']['worst_sellers'] = $worst_sellers_res->fetch_all(MYSQLI_ASSOC);

    // Low Stock
    $low_stock_res = $conn->query("SELECT i.Name, i.UnitOfMeasure, inv.QuantityInStock FROM inventory inv JOIN ingredients i ON inv.IngredientID = i.IngredientID WHERE inv.QuantityInStock < 10 ORDER BY inv.QuantityInStock ASC LIMIT 5");
    $data['inventory_insights']['low_stock'] = $low_stock_res->fetch_all(MYSQLI_ASSOC);

    // =================================================================
    // 5. Active Orders
    // =================================================================
    $active_orders_res = $conn->query("SELECT o.OrderID, t.TableNumber, o.TotalAmount, o.OrderStatus FROM orders o JOIN restaurant_tables t ON o.TableID = t.TableID WHERE o.OrderStatus IN ('Pending', 'In-Progress') ORDER BY o.OrderTime ASC LIMIT 10");
    $data['active_orders'] = $active_orders_res->fetch_all(MYSQLI_ASSOC);

    $conn->commit();
    $response = ['success' => true, 'data' => $data];

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);