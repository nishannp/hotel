<?php 
require_once 'includes/header.php'; 
?>

<link rel="stylesheet" href="css/store_sales_style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<div class="page-container">
    <div class="page-header">
        <h2>Store Sales</h2>
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
                            <label for="saleCategory">Filter by Category</label>
                            <select id="saleCategory" name="category_id">
                                <option value="0">All Categories</option>
                                <!-- Categories will be loaded here via JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="storeItem">Item</label>
                            <select id="storeItem" name="store_item_id" required disabled>
                                <option value="">Select a category first...</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="itemPrice">Price ($)</label>
                                <input type="text" id="itemPrice" readonly placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label for="saleQuantity">Quantity</label>
                                <input type="number" id="saleQuantity" name="quantity" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="form-group total-display">
                            <strong>Total: $<span id="totalAmountDisplay">0.00</span></strong>
                        </div>
                        <button type="submit" class="btn-primary" disabled>
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
                         <input type="text" id="logSearch" placeholder="Search by item, category...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="salesLogTable">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Item Name</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Date</th>
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
            <h3>Confirm Deletion</h3>
            <button id="closeConfirmationModalBtn" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this sale record? This action cannot be undone.</p>
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
    document.querySelector('.content-header h1').textContent = 'Store Sales';

    const SalesApp = {
        elements: {
            form: document.getElementById('saleForm'),
            categorySelect: document.getElementById('saleCategory'),
            itemSelect: document.getElementById('storeItem'),
            itemPrice: document.getElementById('itemPrice'),
            quantityInput: document.getElementById('saleQuantity'),
            totalDisplay: document.getElementById('totalAmountDisplay'),
            submitButton: document.querySelector('#saleForm button[type="submit"]'),
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
            storeItems: [],
            searchTerm: '',
            deleteSaleId: null,
        },

        init() {
            this.loadCategories();
            this.loadStoreItems(0); // Load all items initially
            this.loadSales();
            this.bindEvents();
        },

        bindEvents() {
            this.elements.form.addEventListener('submit', e => this.handleFormSubmit(e));
            this.elements.categorySelect.addEventListener('change', e => this.loadStoreItems(e.target.value));
            this.elements.itemSelect.addEventListener('change', e => this.updatePriceAndTotal());
            this.elements.quantityInput.addEventListener('input', e => this.updatePriceAndTotal());
            
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

            this.elements.confirmDeleteBtn.addEventListener('click', () => this.executeDelete());
            this.elements.cancelDeleteBtn.addEventListener('click', () => this.hideConfirmationModal());
            this.elements.closeConfirmationModalBtn.addEventListener('click', () => this.hideConfirmationModal());
        },

        async loadCategories() {
            try {
                const response = await fetch('ajax/ajax_handler_store_categories.php?action=fetchAll');
                const data = await response.json();
                if (data.success) {
                    const select = this.elements.categorySelect;
                    select.innerHTML = '<option value="0">All Categories</option>';
                    data.data.forEach(cat => {
                        select.innerHTML += `<option value="${cat.CategoryID}">${this.escapeHTML(cat.CategoryName)}</option>`;
                    });
                } else {
                    this.showToast('Failed to load store categories.', 'error');
                }
            } catch (error) {
                this.showToast('An error occurred while fetching categories.', 'error');
            }
        },

        async loadStoreItems(categoryId) {
            try {
                const response = await fetch(`ajax/ajax_handler_store_sales.php?action=fetchStoreItems&category_id=${categoryId}`);
                const data = await response.json();
                if (data.success) {
                    this.state.storeItems = data.data;
                    this.populateItemSelect();
                } else {
                    this.showToast('Failed to load store items.', 'error');
                }
            } catch (error) {
                this.showToast('An error occurred while fetching items.', 'error');
            }
        },

        populateItemSelect() {
            const select = this.elements.itemSelect;
            select.innerHTML = '<option value="">Select an item...</option>';
            if (this.state.storeItems.length > 0) {
                this.state.storeItems.forEach(item => {
                    select.innerHTML += `<option value="${item.StoreItemID}" data-price="${item.Price}">${this.escapeHTML(item.Name)}</option>`;
                });
                select.disabled = false;
            } else {
                select.innerHTML = '<option value="">No items in this category</option>';
                select.disabled = true;
            }
            this.updatePriceAndTotal();
        },

        updatePriceAndTotal() {
            const selectedOption = this.elements.itemSelect.options[this.elements.itemSelect.selectedIndex];
            const price = selectedOption ? selectedOption.dataset.price || 0 : 0;
            const quantity = parseInt(this.elements.quantityInput.value) || 0;
            
            this.elements.itemPrice.value = parseFloat(price).toFixed(2);
            this.elements.totalDisplay.textContent = (price * quantity).toFixed(2);

            this.elements.submitButton.disabled = !selectedOption || !selectedOption.value;
        },

        async loadSales() {
            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php?action=fetchAll');
                const data = await response.json();
                this.state.sales = data.success ? data.data : [];
                this.renderSales();
            } catch (error) {
                this.showToast('Failed to load sales log.', 'error');
            }
        },

        renderSales() {
            this.elements.logTableBody.innerHTML = '';
            const filteredSales = this.state.sales.filter(sale => {
                const categoryName = sale.CategoryName ? sale.CategoryName.toLowerCase() : '';
                const itemName = sale.ItemName ? sale.ItemName.toLowerCase() : '';
                return categoryName.includes(this.state.searchTerm) ||
                       itemName.includes(this.state.searchTerm);
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
                            <td>${this.escapeHTML(sale.ItemName)}</td>
                            <td>${sale.Quantity}</td>
                            <td>${parseFloat(sale.SalePrice).toFixed(2)}</td>
                            <td>${parseFloat(sale.TotalAmount).toFixed(2)}</td>
                            <td>${new Date(sale.SaleTime).toLocaleString()}</td>
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
                    this.updatePriceAndTotal();
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

        showToast(message, type = 'success') {
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
