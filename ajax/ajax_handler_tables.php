<?php
// ajax/ajax_handler_tables.php - Updated for Party System
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        // Fetch all tables
        $sql_tables = "SELECT TableID, TableNumber, Capacity, Status FROM restaurant_tables ORDER BY TableNumber";
        $result_tables = $conn->query($sql_tables);
        $tables = [];
        while ($row = $result_tables->fetch_assoc()) {
            $tables[$row['TableID']] = $row;
            $tables[$row['TableID']]['Parties'] = []; // Initialize parties array
        }

        // Fetch all active parties and group them by TableID
        $sql_parties = "SELECT TableID, PartyIdentifier, NumberOfGuests, PartyStatus 
                        FROM customer_parties 
                        WHERE PartyStatus != 'Departed'";
        $result_parties = $conn->query($sql_parties);
        if ($result_parties) {
            while ($party_row = $result_parties->fetch_assoc()) {
                if (isset($tables[$party_row['TableID']])) {
                    $tables[$party_row['TableID']]['Parties'][] = $party_row;
                }
            }
        }
        
        $response = ['success' => true, 'data' => array_values($tables)];
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
                 $response['message'] = 'Cannot delete this table. It has active or past parties associated with it.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);
