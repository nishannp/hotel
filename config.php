<?php
// config.php

// --- IMPORTANT: Set your database credentials here ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your database username (e.g., 'root')
define('DB_PASSWORD', '');     // Your database password
define('DB_NAME', 'ashish_hotel');  // The name of your database

// Create a new mysqli connection object
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    // If connection fails, stop the script and show an error.
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset to utf8mb4 for full unicode support
$conn->set_charset("utf8mb4");

?>