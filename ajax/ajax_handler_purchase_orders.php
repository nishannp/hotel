<?php
require_once '../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT po.*, s.SupplierName 
                FROM purchase_orders po
                JOIN suppliers s ON po.SupplierID = s.SupplierID
                ORDER BY po.OrderDate DESC, po.PurchaseOrderID DESC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $response = ['success' => true, 'data' => $data];
        break;

    case 'fetchSingle':
        $id = $_GET['id'] ?? 0;
        $po_sql = "SELECT * FROM purchase_orders WHERE PurchaseOrderID = ?";
        $stmt_po = $conn->prepare($po_sql);
        $stmt_po->bind_param("i", $id);
        $stmt_po->execute();
        $po = $stmt_po->get_result()->fetch_assoc();
        $stmt_po->close();

        $items_sql = "SELECT * FROM purchase_order_details WHERE PurchaseOrderID = ?";
        $stmt_items = $conn->prepare($items_sql);
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_items->close();

        $response = ['success' => true, 'data' => ['po' => $po, 'items' => $items]];
        break;

    case 'save':
        $conn->begin_transaction();
        try {
            $id = $_POST['po_id'] ?? null;
            $supplier_id = $_POST['supplier_id'];
            $order_date = $_POST['order_date'];
            $status = $_POST['status'];
            $expected_date = !empty($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : null;
            $actual_date = !empty($_POST['actual_delivery_date']) ? $_POST['actual_delivery_date'] : null;
            $items = json_decode($_POST['items'], true);

            if (empty($supplier_id) || empty($order_date) || empty($status) || empty($items)) {
                throw new Exception("Missing required fields.");
            }

            if (empty($id)) { // ADD
                $sql = "INSERT INTO purchase_orders (SupplierID, OrderDate, ExpectedDeliveryDate, ActualDeliveryDate, Status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $supplier_id, $order_date, $expected_date, $actual_date, $status);
                $message = "Purchase Order created successfully.";
            } else { // UPDATE
                $sql = "UPDATE purchase_orders SET SupplierID = ?, OrderDate = ?, ExpectedDeliveryDate = ?, ActualDeliveryDate = ?, Status = ? WHERE PurchaseOrderID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssi", $supplier_id, $order_date, $expected_date, $actual_date, $status, $id);
                $message = "Purchase Order updated successfully.";
            }
            
            $stmt->execute();
            $po_id = empty($id) ? $stmt->insert_id : $id;
            $stmt->close();

            $stmt_delete = $conn->prepare("DELETE FROM purchase_order_details WHERE PurchaseOrderID = ?");
            $stmt_delete->bind_param("i", $po_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $item_sql = "INSERT INTO purchase_order_details (PurchaseOrderID, IngredientID, QuantityOrdered, UnitPrice) VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($item_sql);
            foreach ($items as $item) {
                $stmt_item->bind_param("iidd", $po_id, $item['ingredient_id'], $item['quantity'], $item['unit_price']);
                $stmt_item->execute();
            }
            $stmt_item->close();
            
            $conn->commit();
            $response = ['success' => true, 'message' => $message];

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;

    case 'delete':
        $conn->begin_transaction();
        try {
            $po_id = $_POST['po_id'] ?? 0;
            if (empty($po_id)) {
                throw new Exception("Purchase Order ID is required.");
            }

            $stmt_check = $conn->prepare("SELECT Status FROM purchase_orders WHERE PurchaseOrderID = ?");
            $stmt_check->bind_param("i", $po_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Purchase Order not found.");
            }
            $status = $result->fetch_assoc()['Status'];
            $stmt_check->close();

            if ($status !== 'Draft') {
                throw new Exception("Only orders in 'Draft' status can be deleted.");
            }

            $stmt_delete = $conn->prepare("DELETE FROM purchase_orders WHERE PurchaseOrderID = ?");
            $stmt_delete->bind_param("i", $po_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            $conn->commit();
            $response = ['success' => true, 'message' => 'Draft purchase order deleted successfully.'];

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
}

$conn->close();
echo json_encode($response);