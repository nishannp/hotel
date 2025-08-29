<?php
// generate_report.php
require_once 'config.php';

// --- Security & Validation ---
$start_date_str = $_GET['startDate'] ?? '';
$end_date_str = $_GET['endDate'] ?? '';

// Basic validation to ensure dates are in the expected Y-m-d format
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date_str) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date_str)) {
    die("Invalid date format. Please use YYYY-MM-DD.");
}

// --- Database Query ---
$sql = "SELECT 
            o.OrderID, 
            o.OrderTime, 
            o.OrderStatus, 
            o.TotalAmount,
            t.TableNumber,
            cp.PartyIdentifier,
            s.FirstName AS StaffFirstName,
            s.LastName AS StaffLastName,
            GROUP_CONCAT(CONCAT(od.Quantity, 'x ', mi.Name) SEPARATOR '; ') AS ItemDetails
        FROM orders o
        JOIN customer_parties cp ON o.PartyID = cp.PartyID
        JOIN restaurant_tables t ON cp.TableID = t.TableID
        JOIN staff s ON o.StaffID = s.StaffID
        JOIN order_details od ON o.OrderID = od.OrderID
        JOIN menu_items mi ON od.MenuItemID = mi.MenuItemID
        WHERE o.OrderTime BETWEEN ? AND ?
        GROUP BY o.OrderID
        ORDER BY o.OrderTime ASC";

$stmt = $conn->prepare($sql);
$start_datetime = $start_date_str . ' 00:00:00';
$end_datetime = $end_date_str . ' 23:59:59';
$stmt->bind_param("ss", $start_datetime, $end_datetime);
$stmt->execute();
$result = $stmt->get_result();

// --- CSV Generation ---
$filename = "orders_report_{$start_date_str}_to_{$end_date_str}.csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Add headers to the CSV file
fputcsv($output, [
    'Order ID',
    'Date & Time',
    'Status',
    'Total Amount',
    'Table Number',
    'Party Identifier',
    'Processed By',
    'Items'
]);

// Loop through the data and add it to the CSV
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['OrderID'],
            $row['OrderTime'],
            $row['OrderStatus'],
            $row['TotalAmount'],
            $row['TableNumber'],
            $row['PartyIdentifier'],
            $row['StaffFirstName'] . ' ' . $row['StaffLastName'],
            $row['ItemDetails']
        ]);
    }
}

fclose($output);
$stmt->close();
$conn->close();
exit();
