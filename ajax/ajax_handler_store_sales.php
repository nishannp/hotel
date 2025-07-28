<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action specified.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT sl.SaleID, sl.TotalAmount, sl.SaleTime, sl.ItemDescription, sc.CategoryName 
                FROM store_sales_log sl
                JOIN store_item_categories sc ON sl.CategoryID = sc.CategoryID
                ORDER BY sl.SaleTime DESC, sl.SaleID DESC";
        $result = $conn->query($sql);
        $sales = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $sales[] = $row;
            }
            $response = ['success' => true, 'data' => $sales];
        } else {
            $response['message'] = 'Error fetching sales log: ' . $conn->error;
        }
        break;

    case 'recordSale':
        $categoryId = $_POST['category_id'] ?? 0;
        $totalAmount = $_POST['TotalAmount'] ?? 0;
        $saleTime = $_POST['SaleTime'] ?? '';
        $itemDescription = trim($_POST['ItemDescription'] ?? '');

        if (empty($categoryId) || empty($totalAmount) || empty($saleTime)) {
            $response['message'] = 'Category, Amount, and Sale Date are required.';
            break;
        }
        if (!is_numeric($totalAmount) || $totalAmount <= 0) {
            $response['message'] = 'Please enter a valid, positive sale amount.';
            break;
        }

        try {
            $sql = "INSERT INTO store_sales_log (CategoryID, TotalAmount, SaleTime, ItemDescription) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idss", $categoryId, $totalAmount, $saleTime, $itemDescription);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Sale recorded successfully.'];
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            // Check for foreign key constraint violation
            if ($conn->errno == 1452) {
                $response['message'] = 'The selected category does not exist.';
            } else {
                $response['message'] = 'An exception occurred: ' . $e->getMessage();
            }
        }
        break;

    case 'deleteSale':
        $saleId = $_POST['sale_id'] ?? 0;
        if ($saleId > 0) {
            $stmt = $conn->prepare("DELETE FROM store_sales_log WHERE SaleID = ?");
            $stmt->bind_param("i", $saleId);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Sale record deleted successfully.'];
            } else {
                $response['message'] = 'Error deleting sale record: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid ID provided for deletion.';
        }
        break;
}

$conn->close();
echo json_encode($response);
