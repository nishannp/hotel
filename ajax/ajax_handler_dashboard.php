<?php
// ajax/ajax_handler_dashboard.php
require_once '../config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

try {
    $data = [];
    $conn->begin_transaction();

    // =================================================================
    // Timeframes
    // =================================================================
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
    $yesterday_end = date('Y-m-d 23:59:59', strtotime('-1 day'));
    $month_start = date('Y-m-01 00:00:00');
    $thirty_days_ago = date('Y-m-d 00:00:00', strtotime('-29 days'));


    // =================================================================
    // 1. KPI Stats & Comparisons
    // =================================================================
    $data['kpi'] = [];
    // Hotel Revenue
    $rev_today_res = $conn->query("SELECT SUM(TotalAmount) as total FROM orders WHERE OrderStatus = 'Completed' AND OrderTime BETWEEN '$today_start' AND '$today_end'");
    $data['kpi']['hotel_revenue_today'] = $rev_today_res->fetch_assoc()['total'] ?? 0;
    $rev_yesterday_res = $conn->query("SELECT SUM(TotalAmount) as total FROM orders WHERE OrderStatus = 'Completed' AND OrderTime BETWEEN '$yesterday_start' AND '$yesterday_end'");
    $data['kpi']['hotel_revenue_yesterday'] = $rev_yesterday_res->fetch_assoc()['total'] ?? 0;

    // Store Revenue
    $store_rev_today_res = $conn->query("SELECT SUM(TotalAmount) as total FROM store_sales_log WHERE SaleTime BETWEEN '$today_start' AND '$today_end'");
    $data['kpi']['store_revenue_today'] = $store_rev_today_res->fetch_assoc()['total'] ?? 0;
    $store_rev_yesterday_res = $conn->query("SELECT SUM(TotalAmount) as total FROM store_sales_log WHERE SaleTime BETWEEN '$yesterday_start' AND '$yesterday_end'");
    $data['kpi']['store_revenue_yesterday'] = $store_rev_yesterday_res->fetch_assoc()['total'] ?? 0;

    // Combined Revenue
    $data['kpi']['total_revenue_today'] = $data['kpi']['hotel_revenue_today'] + $data['kpi']['store_revenue_today'];
    $data['kpi']['total_revenue_yesterday'] = $data['kpi']['hotel_revenue_yesterday'] + $data['kpi']['store_revenue_yesterday'];

    // Orders
    $orders_today_res = $conn->query("SELECT COUNT(OrderID) as count, SUM(CASE WHEN OrderStatus = 'Completed' THEN 1 ELSE 0 END) as completed_count FROM orders WHERE OrderTime BETWEEN '$today_start' AND '$today_end'");
    $orders_today_data = $orders_today_res->fetch_assoc();
    $data['kpi']['orders_today'] = $orders_today_data['count'] ?? 0;
    $data['kpi']['completed_orders_today'] = $orders_today_data['completed_count'] ?? 0;


    // Avg. Order Value
    $data['kpi']['aov_today'] = ($data['kpi']['completed_orders_today'] > 0) ? ($data['kpi']['hotel_revenue_today'] / $data['kpi']['completed_orders_today']) : 0;

    // Avg. Customer Time (Today)
    $avg_time_res = $conn->query("
        SELECT AVG(duration) as avg_duration
        FROM (
            SELECT TIMESTAMPDIFF(MINUTE, o.OrderTime, MAX(p.PaymentTime)) as duration
            FROM orders o
            JOIN payments p ON o.OrderID = p.OrderID
            WHERE o.OrderStatus = 'Completed'
            AND o.OrderTime BETWEEN '$today_start' AND '$today_end'
            GROUP BY o.OrderID
        ) as order_durations
    ");
    $data['kpi']['avg_customer_time_today'] = $avg_time_res->fetch_assoc()['avg_duration'] ?? 0;
    
    // Low Stock Alerts
    $low_stock_alerts_res = $conn->query("SELECT COUNT(AlertID) as count FROM low_stock_alerts WHERE Status = 'Pending'");
    $data['kpi']['low_stock_alerts'] = $low_stock_alerts_res->fetch_assoc()['count'] ?? 0;


    // =================================================================
    // 2. Table Status (for Pie Chart)
    // =================================================================
    $table_status_res = $conn->query("SELECT Status, COUNT(TableID) as count FROM restaurant_tables GROUP BY Status");
    $table_statuses = ['Available' => 0, 'Occupied' => 0, 'Reserved' => 0];
    while($row = $table_status_res->fetch_assoc()) {
        if (array_key_exists($row['Status'], $table_statuses)) {
            $table_statuses[$row['Status']] = (int)$row['count'];
        }
    }
    $data['table_statuses'] = $table_statuses;

    // =================================================================
    // 3. Sales Chart Data (Last 30 Days)
    // =================================================================
    $sales_chart_data = ['labels' => [], 'values' => []];
    // Hotel Sales
    $hotel_sales_res = $conn->query("SELECT DATE(OrderTime) as order_date, SUM(TotalAmount) as daily_total FROM orders WHERE OrderStatus = 'Completed' AND OrderTime >= '$thirty_days_ago' GROUP BY order_date");
    $daily_sales = [];
    while($row = $hotel_sales_res->fetch_assoc()){
        $daily_sales[$row['order_date']] = (float)$row['daily_total'];
    }

    // Store Sales
    $store_sales_res = $conn->query("SELECT DATE(SaleTime) as sale_date, SUM(TotalAmount) as daily_total FROM store_sales_log WHERE SaleTime >= '$thirty_days_ago' GROUP BY sale_date");
    while($row = $store_sales_res->fetch_assoc()){
        if (isset($daily_sales[$row['sale_date']])) {
            $daily_sales[$row['sale_date']] += (float)$row['daily_total'];
        } else {
            $daily_sales[$row['sale_date']] = (float)$row['daily_total'];
        }
    }

    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $sales_chart_data['labels'][] = date('M j', strtotime($date));
        $sales_chart_data['values'][] = $daily_sales[$date] ?? 0;
    }
    $data['sales_chart'] = $sales_chart_data;

    // =================================================================
    // 4. Menu & Inventory Insights
    // =================================================================
    $data['menu_insights'] = [];
    // Best Sellers
    $best_sellers_res = $conn->query("SELECT mi.Name, COUNT(od.MenuItemID) as order_count FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderTime >= '$month_start' GROUP BY od.MenuItemID ORDER BY order_count DESC LIMIT 5");
    $data['menu_insights']['best_sellers'] = $best_sellers_res->fetch_all(MYSQLI_ASSOC);

    // Worst Sellers
    $worst_sellers_res = $conn->query("SELECT mi.Name, COUNT(od.MenuItemID) as order_count FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderTime >= '$month_start' GROUP BY od.MenuItemID ORDER BY order_count ASC LIMIT 5");
    $data['menu_insights']['worst_sellers'] = $worst_sellers_res->fetch_all(MYSQLI_ASSOC);

    // Low Stock
    $low_stock_res = $conn->query("SELECT i.Name, i.UnitOfMeasure, inv.QuantityInStock FROM inventory inv JOIN ingredients i ON inv.IngredientID = i.IngredientID WHERE inv.QuantityInStock < inv.ReorderLevel ORDER BY inv.QuantityInStock ASC LIMIT 5");
    $data['inventory_insights']['low_stock'] = $low_stock_res->fetch_all(MYSQLI_ASSOC);

    // =================================================================
    // 5. Active Orders
    // =================================================================
    $active_orders_res = $conn->query("SELECT o.OrderID, t.TableNumber, o.TotalAmount, o.OrderStatus FROM orders o JOIN restaurant_tables t ON o.TableID = t.TableID WHERE o.OrderStatus IN ('Pending', 'In-Progress') ORDER BY o.OrderTime ASC LIMIT 10");
    $data['active_orders'] = $active_orders_res->fetch_all(MYSQLI_ASSOC);

    // =================================================================
    // 6. Advanced Analytics
    // =================================================================
    $data['analytics'] = [];
    // Sales by Category (This Month)
    $cat_sales_res = $conn->query("SELECT mc.CategoryName, SUM(od.Subtotal) as category_total FROM order_details od JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID JOIN menu_categories mc ON mi.CategoryID = mc.CategoryID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderStatus = 'Completed' AND o.OrderTime >= '$month_start' GROUP BY mc.CategoryID ORDER BY category_total DESC");
    $data['analytics']['category_sales'] = $cat_sales_res->fetch_all(MYSQLI_ASSOC);

    // Peak Hours (This Month)
    $peak_hours_res = $conn->query("SELECT HOUR(OrderTime) as hour, COUNT(OrderID) as order_count FROM orders WHERE OrderTime >= '$month_start' GROUP BY hour ORDER BY hour ASC");
    $peak_hours_data = array_fill(0, 24, 0);
    while($row = $peak_hours_res->fetch_assoc()){
        $peak_hours_data[(int)$row['hour']] = (int)$row['order_count'];
    }
    $data['analytics']['peak_hours'] = $peak_hours_data;

    // Top Staff (This Month)
    $top_staff_res = $conn->query("SELECT s.FirstName, s.LastName, COUNT(o.OrderID) as order_count FROM orders o JOIN staff s ON o.StaffID = s.StaffID WHERE o.OrderTime >= '$month_start' GROUP BY o.StaffID ORDER BY order_count DESC LIMIT 5");
    $data['analytics']['top_staff'] = $top_staff_res->fetch_all(MYSQLI_ASSOC);

    // =================================================================
    // 7. Today's Report Data (ENHANCED)
    // =================================================================
    $data['todays_report'] = [];
    // All orders for today
    $report_orders_res = $conn->query("
        SELECT 
            o.OrderID,
            t.TableNumber,
            o.OrderTime,
            o.TotalAmount,
            o.OrderStatus,
            s.FirstName as StaffFirstName,
            s.LastName as StaffLastName,
            c.FirstName as CustomerFirstName,
            c.LastName as CustomerLastName
        FROM orders o
        JOIN restaurant_tables t ON o.TableID = t.TableID
        JOIN staff s ON o.StaffID = s.StaffID
        LEFT JOIN customers c ON o.CustomerID = c.CustomerID
        WHERE o.OrderTime BETWEEN '$today_start' AND '$today_end'
        ORDER BY o.OrderTime DESC
    ");
    $data['todays_report']['orders'] = $report_orders_res->fetch_all(MYSQLI_ASSOC);

    // All order *details* for today for the drill-down modal
    $order_details_res = $conn->query("
        SELECT 
            od.OrderID,
            mi.Name as ItemName,
            od.Quantity,
            od.Subtotal,
            od.SpecialInstructions
        FROM order_details od
        JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
        WHERE od.OrderID IN (SELECT OrderID FROM orders WHERE OrderTime BETWEEN '$today_start' AND '$today_end')
    ");
    $all_order_details = [];
    while($row = $order_details_res->fetch_assoc()) {
        $all_order_details[$row['OrderID']][] = $row;
    }
    $data['todays_report']['order_details'] = $all_order_details;


    // All menu item sales for today
    $report_menu_sales_res = $conn->query("
        SELECT 
            mi.Name as ItemName,
            mc.CategoryName,
            SUM(od.Quantity) as QuantitySold,
            SUM(od.Subtotal) as TotalRevenue
        FROM order_details od
        JOIN orders o ON od.OrderID = o.OrderID
        JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
        JOIN menu_categories mc ON mi.CategoryID = mc.CategoryID
        WHERE o.OrderTime BETWEEN '$today_start' AND '$today_end' AND o.OrderStatus = 'Completed'
        GROUP BY mi.MenuItemID, mc.CategoryName
        ORDER BY TotalRevenue DESC
    ");
    $data['todays_report']['menu_sales'] = $report_menu_sales_res->fetch_all(MYSQLI_ASSOC);

    // Payment method breakdown for today
    $payment_methods_res = $conn->query("
        SELECT 
            p.PaymentMethod,
            SUM(p.AmountPaid) as TotalAmount
        FROM payments p
        JOIN orders o ON p.OrderID = o.OrderID
        WHERE p.PaymentTime BETWEEN '$today_start' AND '$today_end'
        GROUP BY p.PaymentMethod
    ");
    $data['todays_report']['payment_methods'] = $payment_methods_res->fetch_all(MYSQLI_ASSOC);

    // Staff performance for today
    $staff_performance_res = $conn->query("
        SELECT
            s.FirstName,
            s.LastName,
            COUNT(o.OrderID) as OrderCount,
            SUM(CASE WHEN o.OrderStatus = 'Completed' THEN o.TotalAmount ELSE 0 END) as TotalRevenue
        FROM orders o
        JOIN staff s ON o.StaffID = s.StaffID
        WHERE o.OrderTime BETWEEN '$today_start' AND '$today_end'
        GROUP BY s.StaffID
        ORDER BY TotalRevenue DESC
    ");
    $data['todays_report']['staff_performance'] = $staff_performance_res->fetch_all(MYSQLI_ASSOC);

    // Today's Store Sales
    $report_store_sales_res = $conn->query("
        SELECT
            s.SaleTime,
            sic.CategoryName,
            si.Name as ItemName,
            s.Quantity,
            s.SalePrice,
            s.TotalAmount
        FROM store_sales_log s
        JOIN store_items si ON s.StoreItemID = si.StoreItemID
        JOIN store_item_categories sic ON si.CategoryID = sic.CategoryID
        WHERE s.SaleTime BETWEEN '$today_start' AND '$today_end'
        ORDER BY s.SaleTime DESC
    ");
    $data['todays_report']['store_sales'] = $report_store_sales_res->fetch_all(MYSQLI_ASSOC);


    $conn->commit();
    $response = ['success' => true, 'data' => $data];

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
