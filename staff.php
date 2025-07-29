<?php 
// staff.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<style>
/* Using the same clean, light theme from other pages */
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --danger-hover: #d93737;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --font-family: 'Poppins', sans-serif;
    --drawer-width: 450px;
}

/* Base & Layout */
.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.content-header h1 { color: var(--text-primary) !important; font-weight: 600 !important; }
.page-container { padding: 2rem; }

/* Header & Filters */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.75rem; font-weight: 600; color: var(--text-primary); }
.btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.2s ease, transform 0.1s ease; box-shadow: var(--shadow-sm); }
.btn-primary:hover { background-color: var(--primary-hover); box-shadow: var(--shadow-md); }
.btn-primary:active { transform: scale(0.98); }
.btn-primary .material-icons-outlined { font-size: 20px; }

.filters-bar { display: flex; gap: 1rem; align-items: center; padding: 0.75rem; background-color: var(--bg-content); border-radius: 10px; margin-bottom: 2rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); flex-wrap: wrap; }
.search-bar { display: flex; align-items: center; background-color: var(--bg-main); border-radius: 8px; padding: 0 8px; flex-grow: 1; min-width: 250px; }
.search-bar .material-icons-outlined { color: var(--text-secondary); }
.search-bar input { border: none; background: transparent; padding: 10px; width: 100%; font-size: 0.95rem; }
.search-bar input:focus { outline: none; }
.filter-select { border: 1px solid var(--border-color); background-color: var(--bg-content); padding: 10px; border-radius: 8px; font-size: 0.95rem; color: var(--text-secondary); min-width: 180px; background-color: var(--bg-main); }
.filter-select:focus { outline: none; border-color: var(--primary-color); }

/* Staff Card Grid */
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.staff-card { background-color: var(--bg-content); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: box-shadow 0.3s ease, transform 0.3s ease; display: flex; flex-direction: column; text-align: center; padding: 1.5rem; position: relative; }
.staff-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
.card-avatar-wrapper { width: 120px; height: 120px; margin: 0 auto 1rem; position: relative; }
.card-avatar { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid var(--bg-content); box-shadow: var(--shadow-md); }
.status-badge { position: absolute; bottom: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; }
.status-badge.active { background-color: var(--success-color); }
.status-badge.inactive { background-color: #9ca3af; }
.card-name { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin: 0; }
.card-role { font-size: 0.95rem; color: var(--primary-color); font-weight: 500; margin-bottom: 0.5rem; }
.card-salary-info { display: flex; justify-content: space-around; background-color: #f9fafb; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
.salary-box { text-align: center; }
.salary-box .label { font-size: 0.8rem; color: var(--text-secondary); }
.salary-box .amount { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }
.salary-box .amount.positive { color: var(--success-color); }
.salary-box .amount.negative { color: var(--danger-color); }

.card-actions { display: flex; justify-content: center; gap: 0.5rem; margin-top: auto; }
.action-btn { background: #f3f4f6; border: none; cursor: pointer; padding: 8px; border-radius: 8px; display: flex; align-items: center; gap: 4px; transition: background-color 0.2s ease, color 0.2s ease; font-size: 0.85rem; font-weight: 500; color: var(--text-secondary); }
.action-btn .material-icons-outlined { color: var(--text-secondary); font-size: 18px; transition: color 0.2s ease; }
.action-btn:hover { background-color: #e5e7eb; color: var(--text-primary); }
.action-btn:hover .material-icons-outlined { color: var(--text-primary); }
.action-btn.salary-btn:hover { background-color: #e0e7ff; color: var(--primary-color); }
.action-btn.salary-btn:hover .material-icons-outlined { color: var(--primary-color); }

/* Empty State */
.empty-state { display: none; text-align: center; padding: 4rem 2rem; background-color: var(--bg-content); border: 2px dashed var(--border-color); border-radius: 12px; color: var(--text-secondary); }
.empty-state .material-icons-outlined { font-size: 64px; color: var(--primary-color); opacity: 0.5; }
.empty-state h3 { margin-top: 1rem; font-size: 1.5rem; color: var(--text-primary); }

/* Drawer Styles */
.form-drawer { position: fixed; top: 0; right: 0; width: var(--drawer-width); height: 100%; background-color: var(--bg-content); box-shadow: -10px 0 30px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); z-index: 1050; display: flex; flex-direction: column; }
.form-drawer.is-open { transform: translateX(0); }
.drawer-header { padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
#drawerTitle { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); }
.close-drawer-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; color: var(--text-secondary); transition: all 0.2s ease; }
.close-drawer-btn:hover { background: #e5e7eb; color: var(--text-primary); transform: rotate(90deg); }
.drawer-body { padding: 2rem; overflow-y: auto; flex-grow: 1; }
.drawer-body .form-group { margin-bottom: 1.5rem; }
.drawer-body label, #transactionForm label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary); font-size: 0.9rem; }
.drawer-body input, .drawer-body select,
#transactionForm input, #transactionForm select { 
    width: 100%; 
    padding: 12px 14px; 
    border: 1px solid var(--border-color); 
    border-radius: 8px; 
    background-color: var(--bg-main); 
    transition: all 0.2s ease; 
    font-family: var(--font-family);
    font-size: 0.95rem;
    color: var(--text-primary);
}
.drawer-body input:focus, .drawer-body select:focus,
#transactionForm input:focus, #transactionForm select:focus { 
    outline: none; 
    border-color: var(--primary-color); 
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); 
}

/* Custom styling for date input to make it feel more integrated */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
.checkbox-group { display: flex; align-items: center; gap: 10px; background-color: var(--bg-main); padding: 12px; border-radius: 8px; }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); flex-shrink: 0; background-color: var(--bg-content); }
.drawer-footer button { width: 100%; padding: 14px 22px; }
#imageUploadArea { width: 150px; height: 150px; margin: 0 auto 1rem; border: 2px dashed var(--border-color); border-radius: 50%; cursor: pointer; transition: border-color 0.2s; position: relative; overflow: hidden; background-color: #fcfcfd; }
#imageUploadArea:hover { border-color: var(--primary-color); }
#imagePreview { width: 100%; height: 100%; object-fit: cover; position: absolute; opacity: 0; transition: opacity 0.3s ease; }
#imagePreview.has-image { opacity: 1; }
#imageUploadPlaceholder { color: var(--text-secondary); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; }
#imageUploadPlaceholder .material-icons-outlined { font-size: 48px; color: #cbd5e1; }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); display: flex; justify-content: center; align-items: center; z-index: 1100; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
.modal-overlay.is-visible { opacity: 1; visibility: visible; }
.modal-content { background-color: var(--bg-content); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-lg); width: 90%; max-width: 450px; text-align: center; transform: scale(0.9); transition: transform 0.3s ease; }
.modal-overlay.is-visible .modal-content { transform: scale(1); }
.modal-content h3 { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); margin-top: 0; margin-bottom: 1rem; }
.modal-content p { color: var(--text-secondary); margin-bottom: 2rem; }
.modal-actions { display: flex; justify-content: center; gap: 1rem; }
.modal-actions button { padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem; border: none; transition: background-color 0.2s ease; }
.btn-danger { background-color: var(--danger-color); color: white; }
.btn-danger:hover { background-color: var(--danger-hover); }
.btn-secondary { background-color: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); }
.btn-secondary:hover { background-color: #e5e7eb; }

/* Ledger Modal */
#ledgerModal .modal-content { max-width: 700px; text-align: left; }
#ledgerModal .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem; }
#ledgerModal .modal-title { font-size: 1.5rem; font-weight: 600; }
#ledgerModal .modal-close-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; color: var(--text-secondary); transition: all 0.2s ease; }
#ledgerModal .modal-close-btn:hover { background: #e5e7eb; color: var(--text-primary); transform: rotate(90deg); }
#ledgerModal .modal-body { max-height: 70vh; overflow-y: auto; }
.ledger-summary { display: flex; justify-content: space-between; background-color: var(--bg-main); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
.summary-item h4 { margin: 0 0 0.25rem 0; font-size: 0.9rem; color: var(--text-secondary); font-weight: 500; }
.summary-item p { margin: 0; font-size: 1.5rem; font-weight: 600; }
#transactionForm { border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
#transactionForm .form-group { margin: 0; }
#transactionForm .full-width { grid-column: 1 / -1; }
.ledger-table { width: 100%; border-collapse: collapse; }
.ledger-table th, .ledger-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-color); }
.ledger-table th { font-weight: 600; font-size: 0.9rem; color: var(--text-secondary); background-color: #f9fafb; }
.ledger-table .credit-col { color: var(--success-color); }
.ledger-table .debit-col { color: var(--danger-color); }
.ledger-table .delete-txn-btn { background: transparent; border: none; color: var(--text-secondary); cursor: pointer; }
.ledger-table .delete-txn-btn:hover { color: var(--danger-color); }

/* Toast Notifications */
#toastContainer { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end; gap: 1rem; }
.toast { color: white; padding: 15px 25px; border-radius: 8px; box-shadow: var(--shadow-lg); display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.5s ease; opacity: 0; }
.toast.show { transform: translateX(0); opacity: 1; }
.toast.toast-success { background-color: var(--success-color); }
.toast.toast-success .material-icons-outlined { color: white; }
.toast.toast-error { background-color: var(--danger-color); }
.toast.toast-error .material-icons-outlined { color: white; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2 id="main-title">Staff Management</h2>
        <button id="addItemBtn" class="btn-primary">
            <span class="material-icons-outlined">person_add</span>
            Add Staff
        </button>
    </div>

    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search by name...">
        </div>
        <select id="roleFilter" class="filter-select">
            <option value="">All Roles</option>
            <option value="Waiter">Waiter</option>
            <option value="Chef">Chef</option>
            <option value="Manager">Manager</option>
            <option value="Cashier">Cashier</option>
        </select>
    </div>

    <div id="itemsGrid" class="items-grid"></div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">people</span>
        <h3>No Staff Found</h3>
        <p>Try adjusting your search or add a new staff member.</p>
    </div>
</div>

<!-- Add/Edit Staff Drawer -->
<aside id="itemDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Staff</h3>
        <button class="close-drawer-btn" title="Close"><span class="material-icons-outlined">close</span></button>
    </div>
    <form id="itemForm" class="drawer-body" enctype="multipart/form-data">
        <input type="hidden" id="staffId" name="staff_id">
        <input type="hidden" id="existingImagePath" name="existing_image_path">
        
        <div class="form-group">
            <div id="imageUploadArea" onclick="document.getElementById('itemImage').click();">
                <img id="imagePreview" src="#">
                <div id="imageUploadPlaceholder">
                    <span class="material-icons-outlined">add_a_photo</span>
                    <p style="font-size: 0.8rem; margin-top: 0.5rem;">Upload Photo</p>
                </div>
            </div>
            <input type="file" id="itemImage" name="image" accept="image/*" style="display:none;">
        </div>

        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Waiter">Waiter</option>
                <option value="Chef">Chef</option>
                <option value="Manager">Manager</option>
                <option value="Cashier">Cashier</option>
            </select>
        </div>
        <div class="form-group">
            <label for="monthlySalary">Monthly Salary (Rs)</label>
            <input type="number" id="monthlySalary" name="monthly_salary" step="0.01" required placeholder="e.g., 30000.00">
        </div>
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phone_number" required>
        </div>
        <div class="form-group">
            <label for="hireDate">Hire Date</label>
            <input type="date" id="hireDate" name="hire_date" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <div class="checkbox-group">
                 <input type="checkbox" id="isActive" name="is_active" checked>
                 <label for="isActive" style="margin-bottom: 0; cursor: pointer;">This staff member is currently active</label>
            </div>
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="itemForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Staff Member
        </button>
    </div>
</aside>

<!-- Salary Ledger Modal -->
<div id="ledgerModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="ledgerModalTitle" class="modal-title">Salary Ledger</h3>
            <button id="ledgerModalCloseBtn" class="modal-close-btn" title="Close"><span class="material-icons-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <div class="ledger-summary">
                <div class="summary-item">
                    <h4>Monthly Salary</h4>
                    <p id="ledgerSalary">Rs 0.00</p>
                </div>
                <div class="summary-item">
                    <h4>Total Paid</h4>
                    <p id="ledgerTotalPaid" class="debit-col">Rs 0.00</p>
                </div>
                <div class="summary-item">
                    <h4>Current Balance</h4>
                    <p id="ledgerBalance">Rs 0.00</p>
                </div>
            </div>

            <form id="transactionForm">
                <input type="hidden" id="txnStaffId" name="staff_id">
                <div class="form-group">
                    <label for="txnDate">Date</label>
                    <input type="date" id="txnDate" name="date" required>
                </div>
                <div class="form-group">
                    <label for="txnAmount">Amount (Rs)</label>
                    <input type="number" id="txnAmount" name="amount" step="0.01" required>
                </div>
                <div class="form-group full-width">
                    <label for="txnDesc">Description</label>
                    <input type="text" id="txnDesc" name="description" required>
                </div>
                <div class="form-group">
                    <label for="txnType">Transaction Type</label>
                    <select id="txnType" name="type" required>
                        <option value="Debit">Payment / Advance (Debit)</option>
                        <option value="Credit">Salary / Bonus (Credit)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-primary" style="width: 100%;">Add Transaction</button>
                </div>
            </form>

            <div id="ledgerTableContainer">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal-overlay">
    <div class="modal-content">
        <h3 id="modalTitle">Confirm Action</h3>
        <p id="modalMessage">Are you sure?</p>
        <div class="modal-actions">
            <button id="modalCancelBtn" class="btn-secondary">Cancel</button>
            <button id="modalConfirmBtn" class="btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Staff Management';

    const App = {
        elements: {
            drawer: document.getElementById('itemDrawer'),
            form: document.getElementById('itemForm'),
            grid: document.getElementById('itemsGrid'),
            emptyState: document.getElementById('emptyState'),
            addItemBtn: document.getElementById('addItemBtn'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            imageInput: document.getElementById('itemImage'),
            imagePreview: document.getElementById('imagePreview'),
            imageUploadPlaceholder: document.getElementById('imageUploadPlaceholder'),
            searchInput: document.getElementById('searchInput'),
            roleFilter: document.getElementById('roleFilter'),
            // Confirmation Modal
            confirmationModal: document.getElementById('confirmationModal'),
            modalConfirmBtn: document.getElementById('modalConfirmBtn'),
            modalCancelBtn: document.getElementById('modalCancelBtn'),
            modalTitle: document.getElementById('modalTitle'),
            modalMessage: document.getElementById('modalMessage'),
            // Ledger Modal
            ledgerModal: document.getElementById('ledgerModal'),
            ledgerModalCloseBtn: document.getElementById('ledgerModalCloseBtn'),
            ledgerModalTitle: document.getElementById('ledgerModalTitle'),
            ledgerSalary: document.getElementById('ledgerSalary'),
            ledgerTotalPaid: document.getElementById('ledgerTotalPaid'),
            ledgerBalance: document.getElementById('ledgerBalance'),
            transactionForm: document.getElementById('transactionForm'),
            ledgerTableBody: document.querySelector('.ledger-table tbody'),
            txnStaffId: document.getElementById('txnStaffId'),
            // Toast
            toastContainer: document.getElementById('toastContainer'),
        },
        state: {
            items: [],
            searchTerm: '',
            roleFilter: '',
            itemToDeleteId: null,
            currentLedgerStaffId: null,
        },
        lazyLoader: null,

        init() {
            this.initLazyLoader();
            this.bindEvents();
            this.loadData();
        },
        
        bindEvents() {
            this.elements.addItemBtn.addEventListener('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.addEventListener('click', () => this.closeDrawer());
            this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
            this.elements.imageInput.addEventListener('change', () => this.handleFilePreview());
            this.elements.searchInput.addEventListener('input', (e) => {
                this.state.searchTerm = e.target.value.toLowerCase();
                this.render();
            });
            this.elements.roleFilter.addEventListener('change', (e) => {
                this.state.roleFilter = e.target.value;
                this.render();
            });
            this.elements.grid.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.edit-btn');
                if (editBtn) {
                    e.preventDefault();
                    this.openDrawer(editBtn.dataset.id);
                }
                
                const deleteBtn = e.target.closest('.delete-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    this.handleDeleteClick(deleteBtn.dataset.id);
                }

                const salaryBtn = e.target.closest('.salary-btn');
                if (salaryBtn) {
                    e.preventDefault();
                    this.openLedgerModal(salaryBtn.dataset.id);
                }
            });
            // Confirmation Modal
            this.elements.modalConfirmBtn.addEventListener('click', () => this.confirmDelete());
            this.elements.modalCancelBtn.addEventListener('click', () => this.closeConfirmationModal());
            // Ledger Modal
            this.elements.ledgerModalCloseBtn.addEventListener('click', () => this.closeLedgerModal());
            this.elements.transactionForm.addEventListener('submit', (e) => this.handleAddTransaction(e));
            this.elements.ledgerTableBody.addEventListener('click', (e) => {
                const deleteBtn = e.target.closest('.delete-txn-btn');
                if (deleteBtn) {
                    this.handleDeleteTransaction(deleteBtn.dataset.id);
                }
            });
        },
        
        async loadData() {
            try {
                const response = await fetch('ajax/ajax_handler_staff.php?action=fetchAll');
                const data = await response.json();
                this.state.items = data.success ? data.data : [];
                this.render();
            } catch (error) {
                console.error("Error loading data:", error);
                this.showToast('Failed to load staff data.', 'error');
            }
        },

        render() {
            this.elements.grid.innerHTML = '';
            let filteredItems = this.state.items;

            if (this.state.searchTerm) {
                const searchTerm = this.state.searchTerm;
                filteredItems = filteredItems.filter(item => 
                    `${item.FirstName} ${item.LastName}`.toLowerCase().includes(searchTerm)
                );
            }

            if (this.state.roleFilter) {
                filteredItems = filteredItems.filter(item => item.Role === this.state.roleFilter);
            }

            if (filteredItems.length === 0) {
                this.elements.emptyState.style.display = 'block';
                this.elements.grid.style.display = 'none';
            } else {
                this.elements.emptyState.style.display = 'none';
                this.elements.grid.style.display = 'grid';
                filteredItems.forEach(item => {
                    this.elements.grid.insertAdjacentHTML('beforeend', this.createItemCard(item));
                });
            }
            this.observeImages();
        },

        createItemCard(item) {
            const defaultAvatar = 'https://via.placeholder.com/150/d1d5db/1f2937?text=' + this.escapeHTML(item.FirstName.charAt(0));
            const imageUrl = item.ImageUrl || defaultAvatar;
            const isActive = item.IsActive == 1;
            const balance = parseFloat(item.Balance || 0);
            const balanceClass = balance > 0 ? 'positive' : (balance < 0 ? 'negative' : '');

            return `
                <div class="staff-card">
                    <div class="card-avatar-wrapper">
                        <img data-src="${imageUrl}" alt="${this.escapeHTML(item.FirstName)}" class="card-avatar" style="opacity:0">
                        <span class="status-badge ${isActive ? 'active' : 'inactive'}" title="${isActive ? 'Active' : 'Inactive'}"></span>
                    </div>
                    <h3 class="card-name">${this.escapeHTML(item.FirstName)} ${this.escapeHTML(item.LastName)}</h3>
                    <p class="card-role">${this.escapeHTML(item.Role)}</p>
                    
                    <div class="card-salary-info">
                        <div class="salary-box">
                            <div class="label">Salary</div>
                            <div class="amount">Rs ${parseFloat(item.MonthlySalary).toFixed(2)}</div>
                        </div>
                        <div class="salary-box">
                            <div class="label">Balance</div>
                            <div class="amount ${balanceClass}">Rs ${balance.toFixed(2)}</div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button class="action-btn salary-btn" data-id="${item.StaffID}" title="Manage Salary">
                            <span class="material-icons-outlined">account_balance_wallet</span>
                        </button>
                        <button class="action-btn edit-btn" data-id="${item.StaffID}" title="Edit">
                            <span class="material-icons-outlined">edit</span>
                        </button>
                        <button class="action-btn delete-btn" data-id="${item.StaffID}" title="Delete">
                            <span class="material-icons-outlined">delete_outline</span>
                        </button>
                    </div>
                </div>
            `;
        },

        openDrawer(itemId = null) {
            this.elements.form.reset();
            this.resetImagePreview();
            
            if (itemId) {
                document.getElementById('drawerTitle').textContent = 'Edit Staff Member';
                const item = this.state.items.find(i => i.StaffID == itemId);
                if (item) {
                    document.getElementById('staffId').value = item.StaffID;
                    document.getElementById('firstName').value = item.FirstName;
                    document.getElementById('lastName').value = item.LastName;
                    document.getElementById('role').value = item.Role;
                    document.getElementById('monthlySalary').value = parseFloat(item.MonthlySalary).toFixed(2);
                    document.getElementById('phoneNumber').value = item.PhoneNumber;
                    document.getElementById('hireDate').value = item.HireDate;
                    document.getElementById('isActive').checked = (item.IsActive == 1);
                    document.getElementById('existingImagePath').value = item.RelativeImagePath || '';
                    if (item.ImageUrl) {
                        this.elements.imagePreview.src = item.ImageUrl;
                        this.elements.imagePreview.classList.add('has-image');
                        this.elements.imageUploadPlaceholder.style.opacity = '0';
                    }
                }
            } else {
                document.getElementById('drawerTitle').textContent = 'Add New Staff Member';
                document.getElementById('staffId').value = '';
                document.getElementById('existingImagePath').value = '';
                document.getElementById('isActive').checked = true;
            }
            this.elements.drawer.classList.add('is-open');
        },

        closeDrawer() {
            this.elements.drawer.classList.remove('is-open');
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            formData.append('action', 'save');

            try {
                const response = await fetch('ajax/ajax_handler_staff.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.showToast(data.message || 'Staff member saved successfully.', 'success');
                    this.closeDrawer();
                    await this.loadData();
                } else { throw new Error(data.message); }
            } catch (error) {
                console.error('Error saving item:', error);
                this.showToast(error.message, 'error');
            }
        },

        handleDeleteClick(itemId) {
            this.state.itemToDeleteId = itemId;
            const item = this.state.items.find(i => i.StaffID == itemId);
            if (item) {
                this.elements.modalTitle.textContent = 'Confirm Deletion';
                this.elements.modalMessage.innerHTML = `Are you sure you want to delete <strong>${this.escapeHTML(item.FirstName)} ${this.escapeHTML(item.LastName)}</strong>? This action cannot be undone.`;
                this.elements.confirmationModal.classList.add('is-visible');
            }
        },

        closeConfirmationModal() {
            this.elements.confirmationModal.classList.remove('is-visible');
            this.state.itemToDeleteId = null;
        },

        async confirmDelete() {
            if (!this.state.itemToDeleteId) return;
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', this.state.itemToDeleteId);
                const response = await fetch('ajax/ajax_handler_staff.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.showToast(data.message || 'Staff member deleted.', 'success');
                    await this.loadData();
                } else { throw new Error(data.message); }
            } catch (error) {
                this.showToast(error.message, 'error');
            } finally {
                this.closeConfirmationModal();
            }
        },

        // Ledger Modal Functions
        async openLedgerModal(staffId) {
            this.state.currentLedgerStaffId = staffId;
            const staff = this.state.items.find(s => s.StaffID == staffId);
            if (!staff) return;

            this.elements.ledgerModalTitle.textContent = `Ledger for ${this.escapeHTML(staff.FirstName)} ${this.escapeHTML(staff.LastName)}`;
            this.elements.txnStaffId.value = staffId;
            document.getElementById('txnDate').valueAsDate = new Date(); // Set to today
            this.elements.transactionForm.reset(); // Clear form fields

            this.elements.ledgerModal.classList.add('is-visible');
            await this.refreshLedger(staffId);
        },

        async refreshLedger(staffId) {
            try {
                const response = await fetch(`ajax/ajax_handler_staff.php?action=fetchLedger&staff_id=${staffId}`);
                const result = await response.json();
                if (!result.success) throw new Error(result.message);

                const staff = this.state.items.find(s => s.StaffID == staffId);
                const ledgerData = result.data;
                
                let totalCredit = 0;
                let totalDebit = 0;
                
                this.elements.ledgerTableBody.innerHTML = '';
                if (ledgerData.length > 0) {
                    ledgerData.forEach(txn => {
                        const credit = parseFloat(txn.Credit);
                        const debit = parseFloat(txn.Debit);
                        totalCredit += credit;
                        totalDebit += debit;
                        this.elements.ledgerTableBody.innerHTML += `
                            <tr>
                                <td>${txn.TransactionDate}</td>
                                <td>${this.escapeHTML(txn.Description)}</td>
                                <td class="credit-col">${credit > 0 ? `Rs ${credit.toFixed(2)}` : '-'}</td>
                                <td class="debit-col">${debit > 0 ? `Rs ${debit.toFixed(2)}` : '-'}</td>
                                <td>
                                    <button class="delete-txn-btn" data-id="${txn.LedgerID}" title="Delete Transaction">
                                        <span class="material-icons-outlined" style="font-size: 18px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    this.elements.ledgerTableBody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 1rem;">No transactions found.</td></tr>`;
                }

                const balance = totalCredit - totalDebit;
                const balanceClass = balance > 0 ? 'positive' : (balance < 0 ? 'negative' : '');
                
                this.elements.ledgerSalary.textContent = `Rs ${parseFloat(staff.MonthlySalary).toFixed(2)}`;
                this.elements.ledgerTotalPaid.textContent = `Rs ${totalDebit.toFixed(2)}`;
                this.elements.ledgerBalance.textContent = `Rs ${balance.toFixed(2)}`;
                this.elements.ledgerBalance.className = `amount ${balanceClass}`;

            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        closeLedgerModal() {
            this.elements.ledgerModal.classList.remove('is-visible');
            this.state.currentLedgerStaffId = null;
        },

        async handleAddTransaction(event) {
            event.preventDefault();
            const staffId = this.state.currentLedgerStaffId;
            if (!staffId) return;

            const formData = new FormData(this.elements.transactionForm);
            formData.append('action', 'addLedgerTransaction');
            formData.append('staff_id', staffId);

            try {
                const response = await fetch('ajax/ajax_handler_staff.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    this.showToast(result.message, 'success');
                    this.elements.transactionForm.reset();
                    document.getElementById('txnDate').valueAsDate = new Date();
                    await this.refreshLedger(staffId);
                    await this.loadData(); // Reload all staff data to update balances on cards
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        async handleDeleteTransaction(ledgerId) {
            if (!confirm('Are you sure you want to delete this transaction?')) return;
            
            const staffId = this.state.currentLedgerStaffId;
            try {
                const formData = new FormData();
                formData.append('action', 'deleteLedgerTransaction');
                formData.append('ledger_id', ledgerId);
                const response = await fetch('ajax/ajax_handler_staff.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    this.showToast(result.message, 'success');
                    await this.refreshLedger(staffId);
                    await this.loadData(); // Reload all staff data to update balances on cards
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            const icon = document.createElement('span');
            icon.className = 'material-icons-outlined';
            icon.textContent = type === 'success' ? 'check_circle' : 'error';
            const text = document.createElement('span');
            text.textContent = message;
            toast.appendChild(icon);
            toast.appendChild(text);
            this.elements.toastContainer.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 3500);
        },

        handleFilePreview() {
            const file = this.elements.imageInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.elements.imagePreview.src = e.target.result;
                    this.elements.imagePreview.classList.add('has-image');
                    this.elements.imageUploadPlaceholder.style.opacity = '0';
                }
                reader.readAsDataURL(file);
            }
        },

        resetImagePreview() {
            this.elements.imagePreview.src = '#';
            this.elements.imagePreview.classList.remove('has-image');
            this.elements.imageUploadPlaceholder.style.opacity = '1';
            this.elements.imageInput.value = '';
        },
        
        initLazyLoader() {
            this.lazyLoader = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.onload = () => img.style.opacity = 1;
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: "0px 0px 200px 0px" });
        },

        observeImages() {
            const images = this.elements.grid.querySelectorAll('img[data-src]');
            images.forEach(img => this.lazyLoader.observe(img));
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    };
    
    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>
