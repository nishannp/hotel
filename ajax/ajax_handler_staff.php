<?php
// ajax/ajax_handler_staff.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT StaffID, FirstName, LastName, Role, PhoneNumber, HireDate, IsActive FROM staff ORDER BY FirstName, LastName";
        $result = $conn->query($sql);
        $staff = [];
        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }
        $response = ['success' => true, 'data' => $staff];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staffMember = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $staffMember];
        $stmt->close();
        break;

    case 'save': // Handles both add and update
        $id = $_POST['staff_id'] ?? null;
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $role = $_POST['role'];
        $phone = trim($_POST['phone_number']);
        $hire_date = $_POST['hire_date'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($id)) { // ADD
            $sql = "INSERT INTO staff (FirstName, LastName, Role, PhoneNumber, HireDate, IsActive) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $fname, $lname, $role, $phone, $hire_date, $is_active);
            $message = 'Staff member added successfully.';
        } else { // UPDATE
            $sql = "UPDATE staff SET FirstName = ?, LastName = ?, Role = ?, PhoneNumber = ?, HireDate = ?, IsActive = ? WHERE StaffID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $fname, $lname, $role, $phone, $hire_date, $is_active, $id);
            $message = 'Staff member updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM staff WHERE StaffID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Staff member deleted successfully.'];
        } else {
            // Check for foreign key constraint error
            if($conn->errno == 1451) {
                 $response['message'] = 'Cannot delete this staff member. They are assigned to existing orders or payments.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);