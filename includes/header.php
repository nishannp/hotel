<?php
// includes/header.php

// ALWAYS start the session at the very top of the page
session_start();

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit; // Stop further script execution
}

// Use htmlspecialchars to prevent XSS attacks when displaying user data
$username = htmlspecialchars($_SESSION['username']);

// Simple logic to determine the active page for styling the nav link
function isActive($page) {
    // basename($_SERVER['PHP_SELF']) gets the current script's filename
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Management</title>
    
    <!-- Link to our new CSS stylesheet -->

    
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <!-- In includes/header.php, inside <head> -->
<link rel="stylesheet" href="css/admin_style.css">
<link rel="stylesheet" href="css/modal_style.css"> <!-- ADD THIS LINE -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- Select2 Library for better dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

</head>
<body>

    <!-- The Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Hotel Admin</h2>
        </div>
      
        <ul class="sidebar-nav">
            <li>
                <a href="dashboard.php" class="<?php echo isActive('dashboard.php'); ?>">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>

           <li>
                <a href="pos.php" class="<?php echo isActive('pos.php'); ?>">
                    <i class="fa-solid fa-cash-register"></i> <strong>POS Terminal</strong>
                </a>
            </li>
            <li>
                <a href="orders.php" class="<?php echo isActive('orders.php'); ?>">
                    <i class="fa-solid fa-receipt"></i> All Orders
                </a>
            </li>
            <!-- END NEW/REVISED LINKS -->
            <li>
                <a href="payments.php" class="<?php echo isActive('payments.php'); ?>">
                    <i class="fa-solid fa-credit-card"></i> Payments
                </a>
            </li>

            <li>
                <a href="customers.php" class="<?php echo isActive('customers.php'); ?>">
                    <i class="fa-solid fa-users"></i> Customers
                </a>
            </li>

            <!-- NEW/UPDATED LINKS START HERE -->
            <li>
                <a href="menu_categories.php" class="<?php echo isActive('menu_categories.php'); ?>">
                    <i class="fa-solid fa-tags"></i> Menu Categories
                </a>
            </li>
            <li>
                <a href="menu_items.php" class="<?php echo isActive('menu_items.php'); ?>">
                    <i class="fa-solid fa-utensils"></i> Menu Items
                </a>
            </li>
            <li>
                <a href="store_categories.php" class="<?php echo isActive('store_categories.php'); ?>">
                    <i class="fa-solid fa-store"></i> Store Categories
                </a>
            </li>

             <li>
                <a href="store_items.php" class="<?php echo isActive('store_items.php'); ?>">
                    <i class="fa-solid fa-dollar-sign"></i> Store Item
                </a>
            </li>

            <li>
                <a href="store_sales.php" class="<?php echo isActive('store_sales.php'); ?>">
                    <i class="fa-solid fa-dollar-sign"></i> Store Sales
                </a>
            </li>
            <!-- NEW/UPDATED LINKS END HERE -->

            <!--
  <li>
                <a href="ingredients.php" class="<?php echo isActive('ingredients.php'); ?>">
                    <i class="fa-solid fa-seedling"></i> Ingredients
                </a>
            </li>
            <li>
                <a href="suppliers.php" class="<?php echo isActive('suppliers.php'); ?>">
                    <i class="fa-solid fa-truck-field"></i> Suppliers
                </a>
            </li>
            <li>
                <a href="purchase_orders.php" class="<?php echo isActive('purchase_orders.php'); ?>">
                    <i class="fa-solid fa-shopping-cart"></i> Purchase Orders
                </a>
            </li>
            <li>
                <a href="low_stock.php" class="<?php echo isActive('low_stock.php'); ?>">
                    <i class="fa-solid fa-exclamation-triangle"></i> Low Stock Alerts
                </a>
            </li>

-->
             <li>
                <a href="staff.php" class="<?php echo isActive('staff.php'); ?>">
                    <i class="fa-solid fa-user-tie"></i> Staff
                </a>
            </li>
             <li>
                <a href="tables.php" class="<?php echo isActive('tables.php'); ?>">
                    <i class="fa-solid fa-chair"></i> Tables
                </a>
            </li>
            <!-- NEW PHASE 2 LINKS END HERE -->
           
            <li class="logout-link">
                <a href="logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- The Main Content Area -->
    <main class="main-content">
        <header class="content-header">
            <h1>Dashboard</h1>
            <div class="user-info">
                Welcome, <strong><?php echo $username; ?></strong>!
            </div>
        </header>