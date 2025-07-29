<?php
// config.php

// --- Timezone Configuration ---
// Set the default timezone to Kathmandu, Nepal
date_default_timezone_set('Asia/Kathmandu');

// --- IMPORTANT: Set your database credentials here ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your database username (e.g., 'root')
define('DB_PASSWORD', '');     // Your database password
define('DB_NAME', 'ashish_hotel');  // The name of your database

// --- Site and Upload Configuration ---
// Base URL of the site. Ends with a slash '/'.
define('SITE_URL', 'http://localhost/hotel/');
// The absolute path to the uploads directory. Ends with a slash '/'.
define('UPLOADS_DIR', __DIR__ . '/uploads/');
// The public URL for the uploads directory. Ends with a slash '/'.
define('UPLOADS_URL', SITE_URL . 'uploads/');


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
