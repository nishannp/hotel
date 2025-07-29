<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action specified.'];

switch ($action) {
    case 'fetchStoreItems':
        $categoryId = $_GET['category_id'] ?? 0;
        $items = [];
        $sql = "SELECT StoreItemID, Name, Price FROM store_items WHERE IsAvailable = TRUE";
        if ($categoryId > 0) {
            $sql .= " AND CategoryID = ?";
        }
        $sql .= " ORDER BY Name";
        
        $stmt = $conn->prepare($sql);
        if ($categoryId > 0) {
            $stmt->bind_param("i", $categoryId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        $response = ['success' => true, 'data' => $items];
        break;

    case 'fetchAll':
        $sql = "SELECT sl.SaleID, sl.Quantity, sl.SalePrice, sl.TotalAmount, sl.SaleTime,
                       si.Name AS ItemName, sic.CategoryName 
                FROM store_sales_log sl
                JOIN store_items si ON sl.StoreItemID = si.StoreItemID
                JOIN store_item_categories sic ON si.CategoryID = sic.CategoryID
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
        $storeItemId = $_POST['store_item_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;

        if (empty($storeItemId) || empty($quantity)) {
            $response['message'] = 'Store Item and Quantity are required.';
            break;
        }
        if (!is_numeric($quantity) || $quantity <= 0) {
            $response['message'] = 'Please enter a valid, positive quantity.';
            break;
        }

        // Get the current price of the item to store with the sale record
        $priceStmt = $conn->prepare("SELECT Price FROM store_items WHERE StoreItemID = ?");
        $priceStmt->bind_param("i", $storeItemId);
        $priceStmt->execute();
        $result = $priceStmt->get_result();
        if ($result->num_rows === 0) {
            $response['message'] = 'Selected store item not found.';
            $priceStmt->close();
            break;
        }
        $item = $result->fetch_assoc();
        $salePrice = $item['Price'];
        $priceStmt->close();

        try {
            $sql = "INSERT INTO store_sales_log (StoreItemID, Quantity, SalePrice) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iid", $storeItemId, $quantity, $salePrice);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Sale recorded successfully.'];
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = 'An exception occurred: ' . $e->getMessage();
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