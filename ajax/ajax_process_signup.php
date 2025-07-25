<?php
// ajax_process_signup.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
require_once '../config.php';

// Set the response header to application/json
header('Content-Type: application/json');

// Initialize the response array
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// --- Basic Input Validation ---
if (empty($_POST['username']) || empty($_POST['phone_number']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit;
}

if ($_POST['password'] !== $_POST['confirm_password']) {
    $response['message'] = 'Passwords do not match.';
    echo json_encode($response);
    exit;
}

// Sanitize and assign variables
$username = trim($_POST['username']);
$phoneNumber = trim($_POST['phone_number']);
$password = $_POST['password'];

// Hash the password for secure storage
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// --- Database Interaction using PREPARED STATEMENTS ---
// Prepare an SQL statement to prevent SQL injection
$sql = "INSERT INTO admins (Username, PhoneNumber, PasswordHash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $response['message'] = 'Database error: could not prepare statement.';
    error_log('MySQL prepare error: ' . $conn->error); // Log error for admin
    echo json_encode($response);
    exit;
}

// Bind the variables to the prepared statement as parameters
$stmt->bind_param("sss", $username, $phoneNumber, $passwordHash);

// Execute the statement and check for errors
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Signup successful! You can now log in.';
} else {
    // Check for a duplicate entry error (MySQL error code 1062)
    if ($conn->errno == 1062) {
        $response['message'] = 'This username or phone number is already taken.';
    } else {
        $response['message'] = 'An error occurred during signup. Please try again.';
        error_log('MySQL execute error: ' . $stmt->error); // Log error for admin
    }
}

// Close the statement and the connection
$stmt->close();
$conn->close();

// Return the response as JSON
echo json_encode($response);

?>