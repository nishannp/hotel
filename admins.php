<?php 
require_once 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/admins_style.css">
    
</head>
<body>

<div class="main-content">
    <header class="page-header">
        <h1><i class="fas fa-user-shield"></i> Manage Administrators</h1>
        <p>Create, view, and manage system administrators.</p>
    </header>

    <div class="admins-container">
        <div class="card-header">
            <h2><i class="fas fa-users-cog"></i> System Administrators</h2>
            <button id="btn-show-add-modal" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Admin</button>
        </div>
        <div class="card-body">
            <div id="admins-list-container">
                <!-- Admin list will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div id="modal-add-admin" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2><i class="fas fa-user-plus"></i> Create New Admin</h2>
        <form id="form-add-admin">
            <div class="form-group">
                <label for="add-username">Username</label>
                <input type="text" id="add-username" name="username" required>
            </div>
            <div class="form-group">
                <label for="add-phone">Phone Number (Optional)</label>
                <input type="text" id="add-phone" name="phone_number">
            </div>
            <div class="form-group">
                <label for="add-password">Password</label>
                <input type="password" id="add-password" name="password" required>
            </div>
            <div class="form-group">
                <label for="add-password-confirm">Confirm Password</label>
                <input type="password" id="add-password-confirm" name="password_confirm" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary full-width">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="modal-change-password" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2><i class="fas fa-key"></i> Change Password</h2>
        <form id="form-change-password">
            <input type="hidden" id="change-password-admin-id" name="admin_id">
            <p id="change-password-intro">You are changing the password for <strong id="change-password-username"></strong>.</p>
            
            <div class="form-group" id="current-password-group" style="display: none;">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password">
            </div>

            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="password" required>
            </div>
            <div class="form-group">
                <label for="new-password-confirm">Confirm New Password</label>
                <input type="password" id="new-password-confirm" name="password_confirm" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary full-width">Update Password</button>
            </div>
        </form>
    </div>
</div>


<div id="toast"></div>

<script src="js/admins.js"></script>

</body>
</html>
