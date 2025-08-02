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

    <style>
        /* css/admin_style.css (Modern Light Theme) */

/* --- Google Font & Basic Reset --- */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

:root {
    --primary-color: #8b5cf6; /* A vibrant purple for accents */
    --primary-hover-color: #f3f4f6;  /* Very light grey for hover */
    --text-dark-color: #111827;     /* The main dark text color */
    --text-on-primary: #ffffff;     /* White text for on top of the primary color */
    --text-muted-color: #6b7280;    /* A muted grey for secondary text */
    --sidebar-bg: #ffffff;          /* Clean white sidebar */
    --page-bg: #f9fafb;             /* A very light grey for the main content area */
    --border-color: #e5e7eb;        /* A light, subtle border color */
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: var(--page-bg);
    color: var(--text-dark-color); /* Default text is dark */
}

/* --- Modern Light Sidebar --- */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100%;
    background: var(--sidebar-bg);
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    z-index: 100;
    border-right: 1px solid var(--border-color);
}

.sidebar-header {
    padding: 24px 28px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.sidebar-header h2 {
    font-weight: 600;
    font-size: 1.6rem;
    color: var(--text-dark-color); /* Header text is dark */
}

.sidebar-nav {
    flex-grow: 1;
    list-style: none;
    padding: 16px 0;
}

.sidebar-nav li {
    margin: 0 16px;
}

.sidebar-nav li a {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    color: var(--text-muted-color); /* Muted color for inactive links */
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    margin-bottom: 4px;
}

.sidebar-nav li a:hover {
    background: var(--primary-hover-color);
    color: var(--text-dark-color); /* Darken text on hover */
}

/* --- Active Link Style --- */
.sidebar-nav li a.active {
    background: var(--primary-color);
    color: var(--text-on-primary);
    font-weight: 600;
    box-shadow: 0 4px 14px -4px var(--primary-color); /* Add a subtle glow */
}

.sidebar-nav li a i {
    font-size: 1.1rem;
    margin-right: 18px;
    width: 22px;
    text-align: center;
    transition: all 0.2s ease-in-out;
}

/* Keep icon color consistent with text on hover/active */
.sidebar-nav li a:hover i,
.sidebar-nav li a.active i {
    color: inherit;
}

.logout-link {
    margin-top: auto;
    padding-bottom: 16px;
}

/* --- Main Content Area --- */
.main-content {
    margin-left: 260px;
    padding: 30px;
    transition: margin-left 0.3s ease;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-color);
}

.content-header h1 {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--text-dark-color);
}

.user-info {
    font-size: 1rem;
    color: var(--text-muted-color);
}

    </style>
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
                <a href="expenses.php" class="<?php echo isActive('expenses.php'); ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Expenses
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

         
  <li>
                <a href="ingredients.php" class="<?php echo isActive('ingredients.php'); ?>">
                    <i class="fa-solid fa-seedling"></i> Ingredients
                </a>
            </li>


               <!--
            
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
                <a href="admins.php" class="<?php echo isActive('admins.php'); ?>">
                    <i class="fa-solid fa-user-shield"></i> Manage Admins
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