<?php
// ajax_process_login.php

session_start();
require_once '../config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid username or password.'];

// Basic validation
if (empty($_POST['username']) || empty($_POST['password'])) {
    $response['message'] = 'Username and password are required.';
    echo json_encode($response);
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// --- Database Interaction using PREPARED STATEMENTS ---
// Fetch the user from the database
$sql = "SELECT AdminID, Username, PasswordHash FROM admins WHERE Username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $response['message'] = 'Database error: could not prepare statement.';
    error_log('MySQL prepare error: ' . $conn->error);
    echo json_encode($response);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    // Verify the password against the stored hash
    if (password_verify($password, $admin['PasswordHash'])) {
        // Password is correct, start a new session
        session_regenerate_id(true); // Prevent session fixation attacks

        // Store data in session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['admin_id'] = $admin['AdminID'];
        $_SESSION['username'] = $admin['Username'];

        $response['success'] = true;
        // No message needed, the JS will redirect
    }
    // If password_verify fails, the default error message is sent
}
// If num_rows is not 1, the default error message is sent

$stmt->close();
$conn->close();

echo json_encode($response);
?>