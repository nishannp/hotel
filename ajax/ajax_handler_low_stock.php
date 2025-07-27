<?php
require_once '../config.php';
session_start(); // Required to get the StaffID
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT lsa.*, i.Name as IngredientName
                FROM low_stock_alerts lsa
                JOIN ingredients i ON lsa.IngredientID = i.IngredientID
                ORDER BY lsa.Status = 'Pending' DESC, lsa.AlertTime DESC";
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $response = ['success' => true, 'data' => $data];
        break;

    case 'acknowledge':
        $alert_id = $_POST['alert_id'] ?? 0;
        // In a real system, you would get StaffID from the session after they log in.
        // For now, we'll use a placeholder ID, but this is where you'd integrate your staff login.
        $staff_id = $_SESSION['staff_id'] ?? 1; // Placeholder StaffID

        if (empty($alert_id)) {
            $response['message'] = 'Alert ID is required.';
            break;
        }

        $sql = "UPDATE low_stock_alerts 
                SET Status = 'Acknowledged', AcknowledgedByStaffID = ? 
                WHERE AlertID = ? AND Status = 'Pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $staff_id, $alert_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Alert acknowledged.'];
            } else {
                $response['message'] = 'Alert could not be acknowledged. It might have been handled already.';
            }
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);