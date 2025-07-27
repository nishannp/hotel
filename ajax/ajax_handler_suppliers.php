<?php
require_once '../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $result = $conn->query("SELECT SupplierID, SupplierName, ContactPerson, PhoneNumber, IsActive FROM suppliers ORDER BY SupplierName");
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);
        $response = ['success' => true, 'data' => $suppliers];
        break;

    case 'save':
        $id = $_POST['supplier_id'] ?? null;
        $name = trim($_POST['supplier_name']);
        $contact = trim($_POST['contact_person']);
        $phone = trim($_POST['phone_number']);

        if (empty($name) || empty($phone)) {
            $response['message'] = 'Supplier name and phone number are required.';
            echo json_encode($response);
            exit;
        }

        if (empty($id)) { // ADD
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
            $sql = "INSERT INTO suppliers (SupplierName, ContactPerson, PhoneNumber, IsActive) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $contact, $phone, $is_active);
            $message = 'Supplier added successfully.';
        } else { // UPDATE
            // **FIX:** The UPDATE operation should not modify the IsActive status.
            // That is now handled exclusively by the toggleStatus action.
            $sql = "UPDATE suppliers SET SupplierName = ?, ContactPerson = ?, PhoneNumber = ? WHERE SupplierID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $contact, $phone, $id);
            $message = 'Supplier updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'toggleStatus':
        $id = $_POST['supplier_id'] ?? 0;
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

        if (empty($id)) {
            $response['message'] = 'Supplier ID is required.';
            break;
        }

        $sql = "UPDATE suppliers SET IsActive = ? WHERE SupplierID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $is_active, $id);

        if ($stmt->execute()) {
            // **FIX:** Check if a row was actually affected to confirm the update.
            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Supplier status updated.'];
            } else {
                // This can happen if the status was already set to the value, which is not an error.
                $response = ['success' => true, 'message' => 'Supplier status was already up to date.'];
            }
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);