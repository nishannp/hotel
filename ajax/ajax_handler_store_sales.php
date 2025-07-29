<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action specified.'];

switch ($action) {
    case 'fetchStoreItems':
        $sql = "SELECT StoreItemID, Name, Price, ImageUrl, CategoryID FROM store_items WHERE IsAvailable = TRUE ORDER BY Name";
        $result = $conn->query($sql);
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Ensure the image URL is correctly formed
                $row['ImageUrl'] = !empty($row['ImageUrl']) ? '/hotel' . $row['ImageUrl'] : 'https://via.placeholder.com/300/e5e7eb/6b7280?text=No+Image';
                $items[] = $row;
            }
        }
        $response = ['success' => true, 'data' => $items];
        break;

    case 'fetchAllSales':
        $sql = "
            SELECT 
                sl.TransactionID, 
                SUM(sl.Quantity * sl.SalePrice) as GrandTotal, 
                MIN(sl.SaleTime) as SaleTime,
                GROUP_CONCAT(CONCAT(si.Name, ' (x', sl.Quantity, ')') SEPARATOR ', ') as ItemsSummary
            FROM store_sales_log sl
            JOIN store_items si ON sl.StoreItemID = si.StoreItemID
            GROUP BY sl.TransactionID
            ORDER BY SaleTime DESC, sl.TransactionID DESC";
        $result = $conn->query($sql);
        if ($result) {
            $sales = $result->fetch_all(MYSQLI_ASSOC);
            $response = ['success' => true, 'data' => $sales];
        } else {
            $response = ['success' => false, 'message' => 'Failed to fetch sales history: ' . $conn->error];
        }
        break;

    case 'recordSale':
        $cart_json = $_POST['cart'] ?? '[]';
        $cart = json_decode($cart_json, true);

        if (empty($cart) || !is_array($cart)) {
            $response['message'] = 'The cart is empty or invalid.';
            break;
        }

        $conn->begin_transaction();

        try {
            $transaction_id = 'SALE-' . uniqid();
            $sql = "INSERT INTO store_sales_log (TransactionID, StoreItemID, Quantity, SalePrice) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            foreach ($cart as $item) {
                $item_id = $item['id'];
                $quantity = $item['quantity'];
                
                // Get the current price to prevent price-tampering
                $price_res = $conn->query("SELECT Price FROM store_items WHERE StoreItemID = $item_id");
                if ($price_res->num_rows === 0) {
                    throw new Exception("Item with ID $item_id not found.");
                }
                $sale_price = $price_res->fetch_assoc()['Price'];

                $stmt->bind_param("siid", $transaction_id, $item_id, $quantity, $sale_price);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to record sale for item ID $item_id: " . $stmt->error);
                }
            }

            $conn->commit();
            $response = ['success' => true, 'message' => 'Sale recorded successfully!'];

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Transaction failed: ' . $e->getMessage();
        }
        break;

    case 'deleteSale':
        $transactionId = $_POST['transaction_id'] ?? '';
        if (!empty($transactionId)) {
            $stmt = $conn->prepare("DELETE FROM store_sales_log WHERE TransactionID = ?");
            $stmt->bind_param("s", $transactionId);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Sale transaction deleted successfully.'];
            } else {
                $response['message'] = 'Error deleting sale transaction: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Invalid Transaction ID provided for deletion.';
        }
        break;
}

$conn->close();
echo json_encode($response);
