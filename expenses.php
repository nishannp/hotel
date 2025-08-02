<?php 
require_once 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/expenses_style.css">
</head>
<body>

<div class="main-content">
    <header class="page-header">
        <h1>Expenses</h1>
        <p>Track and manage expenses for your business units.</p>
    </header>

    <div class="expenses-layout">
        <!-- Main Content: Add Expense and Recent Expenses -->
        <main class="expenses-main">
            <div class="card form-card">
                <div class="card-header">
                    <h2><i class="fas fa-plus-circle"></i> Record New Expense</h2>
                </div>
                <div class="card-body">
                    <form id="form-add-expense">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expense-business-unit"><i class="fas fa-briefcase"></i> Business Unit</label>
                                <select id="expense-business-unit" name="business_unit_id" class="select2-basic" required>
                                    <!-- Options will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="expense-category"><i class="fas fa-tags"></i> Category</label>
                                <select id="expense-category" name="category_id" class="select2-searchable" required>
                                    <!-- Options will be loaded dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="expense-description"><i class="fas fa-pencil-alt"></i> Description</label>
                            <input type="text" id="expense-description" name="description" placeholder="e.g., November Electricity Bill, 50kg Rice Purchase" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expense-amount"><i class="fas fa-dollar-sign"></i> Amount</label>
                                <input type="number" id="expense-amount" name="amount" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label for="expense-quantity"><i class="fas fa-box"></i> Quantity (Optional)</label>
                                <input type="number" id="expense-quantity" name="quantity" step="0.01" placeholder="e.g., 100">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card recent-expenses-card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Recent Expenses</h2>
                    <div class="filter-controls">
                         <input type="month" id="filter-month" name="filter-month">
                         <button id="btn-refresh-expenses" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="expenses-table-container">
                        <!-- Expenses table will be loaded here -->
                    </div>
                </div>
            </div>
        </main>

        <!-- Sidebar: Manage Categories -->
        <aside class="expenses-sidebar">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-cogs"></i> Manage Categories</h2>
                </div>
                <div class="card-body">
                    <div class="category-management">
                        <form id="form-add-category">
                            <div class="form-group">
                                <label for="category-name">Add New Category</label>
                                <div class="input-group">
                                    <input type="text" id="category-name" name="name" placeholder="e.g., Utilities" required>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </form>
                        <div id="category-list">
                            <!-- Categories will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Modal for editing category -->
<div id="modal-edit-category" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Edit Category</h2>
        <form id="form-edit-category">
            <input type="hidden" id="edit-category-id" name="id">
            <div class="form-group">
                <label for="edit-category-name">Category Name</label>
                <input type="text" id="edit-category-name" name="name" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="js/expenses.js"></script>

</body>
</html>
