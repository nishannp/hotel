<?php
// ajax/ajax_handler_tables.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        // The Status column is important for display
        $sql = "SELECT TableID, TableNumber, Capacity, Status FROM restaurant_tables ORDER BY TableNumber";
        $result = $conn->query($sql);
        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row;
        }
        $response = ['success' => true, 'data' => $tables];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT TableID, TableNumber, Capacity FROM restaurant_tables WHERE TableID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $table = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $table];
        $stmt->close();
        break;

    case 'save': // Handles both add and update
        $id = $_POST['table_id'] ?? null;
        $number = $_POST['table_number'];
        $capacity = $_POST['capacity'];

        // Note: We don't manually set the 'Status'. It defaults to 'Available' on creation
        // and is managed by triggers during operations.

        if (empty($id)) { // ADD
            $sql = "INSERT INTO restaurant_tables (TableNumber, Capacity) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $number, $capacity);
            $message = 'Table added successfully.';
        } else { // UPDATE
            $sql = "UPDATE restaurant_tables SET TableNumber = ?, Capacity = ? WHERE TableID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $number, $capacity, $id);
            $message = 'Table updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
             if($conn->errno == 1062) {
                 $response['message'] = 'Error: A table with this number already exists.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM restaurant_tables WHERE TableID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Table deleted successfully.'];
        } else {
            if($conn->errno == 1451) {
                 $response['message'] = 'Cannot delete this table. It is assigned to existing orders.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);