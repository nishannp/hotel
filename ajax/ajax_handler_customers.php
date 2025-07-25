<?php
// ajax/ajax_handler_customers.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT CustomerID, FirstName, LastName, PhoneNumber, Email FROM customers ORDER BY FirstName, LastName";
        $result = $conn->query($sql);
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        $response = ['success' => true, 'data' => $customers];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT CustomerID, FirstName, LastName, PhoneNumber, Email FROM customers WHERE CustomerID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $response = ['success' => true, 'data' => $customer];
        $stmt->close();
        break;

    case 'save': // This single case handles both ADD and UPDATE
        $id = $_POST['customer_id'] ?? null;
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $phone = trim($_POST['phone_number']);
        $email = trim($_POST['email']);

        // Basic validation
        if (empty($fname) || empty($phone)) {
            $response['message'] = 'First Name and Phone Number are required.';
            echo json_encode($response);
            exit;
        }

        if (empty($id)) { // ADD a new customer
            $sql = "INSERT INTO customers (FirstName, LastName, PhoneNumber, Email) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fname, $lname, $phone, $email);
            $message = 'Customer added successfully.';
        } else { // UPDATE an existing customer
            $sql = "UPDATE customers SET FirstName = ?, LastName = ?, PhoneNumber = ?, Email = ? WHERE CustomerID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $fname, $lname, $phone, $email, $id);
            $message = 'Customer updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            // Handle unique constraint violation for phone or email
            if ($conn->errno == 1062) {
                $response['message'] = 'Error: A customer with this phone number or email already exists.';
            } else {
                $response['message'] = 'Database Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM customers WHERE CustomerID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Customer deleted successfully.'];
        } else {
            // This error occurs if the customer is linked to an order
            if ($conn->errno == 1451) { 
                 $response['message'] = 'Cannot delete this customer. They are linked to existing orders.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);