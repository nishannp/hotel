<?php 
// store_sales.php
require_once 'includes/header.php'; 
?>

<link rel="stylesheet" href="css/store_sales_style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<div class="page-container">
    <div class="page-header">
        <h2>Store Sales Log</h2>
    </div>

    <div class="sales-container">
        <!-- Left Panel: Sales Form -->
        <div class="sales-form-container">
            <div class="form-card">
                <div class="card-header">
                    <h3><span class="material-icons-outlined">add_shopping_cart</span> Record a New Sale</h3>
                </div>
                <div class="card-body">
                    <form id="saleForm">
                        <div class="form-group">
                            <label for="saleCategory">Item Category</label>
                            <select id="saleCategory" name="category_id" required>
                                <option value="">Select a category...</option>
                                <!-- Categories will be loaded here via JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="saleTotalAmount">Sale Amount ($)</label>
                            <input type="number" id="saleTotalAmount" name="TotalAmount" step="0.01" min="0" required placeholder="e.g., 15.50">
                        </div>
                        <div class="form-group" style="display: none;">
                            <label for="saleTime">Date of Sale</label>
                            <input type="hidden" id="saleTime" name="SaleTime" required>
                        </div>
                        <div class="form-group">
                            <label for="saleItemDescription">Notes (Optional)</label>
                            <textarea id="saleItemDescription" name="ItemDescription" rows="3" placeholder="Any details about the sale..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <span class="material-icons-outlined">save</span> Record Sale
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Panel: Sales Log -->
        <div class="sales-log-container">
            <div class="log-card">
                <div class="card-header">
                    <h3><span class="material-icons-outlined">history</span> Recent Sales</h3>
                    <div class="filter-bar">
                         <input type="text" id="logSearch" placeholder="Search logs by category, notes, amount...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="salesLogTable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sales logs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="logEmptyState" class="empty-state" style="display: none;">
                        <span class="material-icons-outlined">receipt_long</span>
                        <h4>No sales recorded yet.</h4>
                        <p>Use the form on the left to add a new sale.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="confirmationTitle">Confirm Deletion</h3>
            <button id="closeConfirmationModalBtn" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmationMessage">Are you sure you want to delete this sale record? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button id="cancelDeleteBtn" class="btn-secondary">Cancel</button>
            <button id="confirmDeleteBtn" class="btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Toast Notification Placeholder -->
<div id="toastNotification" class="toast-notification"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set page title
    document.querySelector('.content-header h1').textContent = 'Store Sales';

    const setSaleDate = () => {
        const now = new Date();
        // Format to YYYY-MM-DD HH:MM:SS for MySQL DATETIME
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        document.getElementById('saleTime').value = formattedDateTime;
    };

    // Set initial sale date/time
    setSaleDate();

    const SalesApp = {
        elements: {
            form: document.getElementById('saleForm'),
            categorySelect: document.getElementById('saleCategory'),
            logTableBody: document.querySelector('#salesLogTable tbody'),
            logEmptyState: document.getElementById('logEmptyState'),
            logSearch: document.getElementById('logSearch'),
            confirmationModal: document.getElementById('confirmationModal'),
            confirmDeleteBtn: document.getElementById('confirmDeleteBtn'),
            cancelDeleteBtn: document.getElementById('cancelDeleteBtn'),
            closeConfirmationModalBtn: document.getElementById('closeConfirmationModalBtn'),
        },
        state: {
            sales: [],
            searchTerm: '',
            deleteSaleId: null,
        },

        init() {
            this.loadCategories();
            this.loadSales();
            this.bindEvents();
        },

        bindEvents() {
            this.elements.form.addEventListener('submit', e => this.handleFormSubmit(e));
            this.elements.logSearch.addEventListener('input', e => {
                this.state.searchTerm = e.target.value.toLowerCase();
                this.renderSales();
            });
            this.elements.logTableBody.addEventListener('click', e => {
                if (e.target.closest('.delete-btn')) {
                    const saleId = e.target.closest('.delete-btn').dataset.id;
                    this.promptDelete(saleId);
                }
            });

            // Modal events
            this.elements.confirmDeleteBtn.addEventListener('click', () => this.executeDelete());
            this.elements.cancelDeleteBtn.addEventListener('click', () => this.hideConfirmationModal());
            this.elements.closeConfirmationModalBtn.addEventListener('click', () => this.hideConfirmationModal());
        },

        async loadCategories() {
            try {
                const response = await fetch('ajax/ajax_handler_store_categories.php?action=fetchAll');
                const data = await response.json();
                if (data.success) {
                    this.elements.categorySelect.innerHTML = '<option value="">Select a category...</option>';
                    data.data.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.CategoryID;
                        option.textContent = this.escapeHTML(cat.CategoryName);
                        this.elements.categorySelect.appendChild(option);
                    });
                } else {
                    this.showToast('Failed to load store categories.', 'error');
                }
            } catch (error) {
                console.error("Error loading categories:", error);
                this.showToast('An error occurred while fetching categories.', 'error');
            }
        },

        async loadSales() {
            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php?action=fetchAll');
                const data = await response.json();
                this.state.sales = data.success ? data.data : [];
                this.renderSales();
            } catch (error) {
                console.error("Error loading sales:", error);
                this.showToast('Failed to load sales log.', 'error');
            }
        },

        renderSales() {
            this.elements.logTableBody.innerHTML = '';
            const filteredSales = this.state.sales.filter(sale => {
                const categoryName = sale.CategoryName ? sale.CategoryName.toLowerCase() : '';
                const description = sale.ItemDescription ? sale.ItemDescription.toLowerCase() : '';
                const amount = sale.TotalAmount ? sale.TotalAmount.toString() : '';
                return categoryName.includes(this.state.searchTerm) ||
                       description.includes(this.state.searchTerm) ||
                       amount.includes(this.state.searchTerm);
            });

            if (filteredSales.length === 0) {
                this.elements.logEmptyState.style.display = 'block';
                this.elements.logTableBody.parentElement.style.display = 'none';
            } else {
                this.elements.logEmptyState.style.display = 'none';
                this.elements.logTableBody.parentElement.style.display = 'table';
                filteredSales.forEach(sale => {
                    const row = `
                        <tr>
                            <td>${this.escapeHTML(sale.CategoryName)}</td>
                            <td>${parseFloat(sale.TotalAmount).toFixed(2)}</td>
                            <td>${new Date(sale.SaleTime).toLocaleString()}</td>
                            <td>${this.escapeHTML(sale.ItemDescription) || 'N/A'}</td>
                            <td>
                                <button class="delete-btn" data-id="${sale.SaleID}" title="Delete Sale">
                                    <span class="material-icons-outlined">delete_outline</span>
                                </button>
                            </td>
                        </tr>
                    `;
                    this.elements.logTableBody.insertAdjacentHTML('beforeend', row);
                });
            }
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            formData.append('action', 'recordSale');

            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.showToast('Sale recorded successfully!', 'success');
                    this.elements.form.reset();
                    setSaleDate(); // Reset date to current time
                    this.loadSales(); // Refresh the log
                } else {
                    throw new Error(data.message || 'Could not record sale.');
                }
            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        promptDelete(saleId) {
            this.state.deleteSaleId = saleId;
            this.showConfirmationModal();
        },

        async executeDelete() {
            if (!this.state.deleteSaleId) return;
            
            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=deleteSale&sale_id=${this.state.deleteSaleId}`
                });
                const data = await response.json();
                this.hideConfirmationModal();
                if (data.success) {
                    this.showToast('Sale record deleted.', 'success');
                    this.loadSales(); // Refresh the log
                } else {
                    throw new Error(data.message || 'Could not delete sale.');
                }
            } catch (error) {
                this.showToast(error.message, 'error');
            } finally {
                this.state.deleteSaleId = null;
            }
        },

        showConfirmationModal() {
            this.elements.confirmationModal.classList.add('show');
        },

        hideConfirmationModal() {
            this.elements.confirmationModal.classList.remove('show');
        },

        showToast(message, type = 'success') { // type can be 'success' or 'error'
            const toast = document.getElementById('toastNotification');
            
            const icon = type === 'success' 
                ? '<span class="material-icons-outlined">check_circle</span>' 
                : '<span class="material-icons-outlined">error</span>';

            toast.innerHTML = `${icon} ${this.escapeHTML(message)}`;
            
            toast.className = `toast-notification show ${type}`;
            
            setTimeout(() => {
                toast.className = 'toast-notification';
            }, 3000);
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    };

    SalesApp.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>