<?php 
require_once 'includes/header.php'; 
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #3b82f6; /* A fresh blue */
    --primary-hover: #2563eb;
    --danger-color: #ef4444;
    --danger-hover: #dc2626;
    --secondary-color: #6b7280;
    --border-color: #e5e7eb;
    --font-family: 'Poppins', sans-serif;
    --drawer-width: 850px;
}

.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.page-container { padding: 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.75rem; font-weight: 600; }

.btn {
    border: none; padding: 12px 22px; border-radius: 8px;
    cursor: pointer; font-weight: 500; font-size: 0.95rem;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.2s ease;
}
.btn-primary { background-color: var(--primary-color); color: white; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
.btn-primary:hover { background-color: var(--primary-hover); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
.btn-danger { background-color: var(--danger-color); color: white; }
.btn-danger:hover { background-color: var(--danger-hover); }
.btn .material-icons-outlined { font-size: 20px; }

.table-container { background-color: var(--bg-content); border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; }
.table-header { 
    padding: 1.5rem; 
    border-bottom: 1px solid var(--border-color); 
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.table-header input { 
    width: 100%; 
    max-width: 350px; 
    padding: 10px 15px; 
    border: 1px solid var(--border-color); 
    border-radius: 8px; 
}
.filter-bar {
    display: flex;
    gap: 0.5rem;
    flex-grow: 1;
    justify-content: flex-end;
}
.filter-btn {
    padding: 8px 16px;
    border: 1px solid var(--border-color);
    background-color: transparent;
    color: var(--secondary-color);
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.filter-btn:hover {
    background-color: #f4f4f5;
    border-color: #d4d4d8;
}
.filter-btn.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
th { background-color: #f9fafb; color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; }
tr:last-child td { border-bottom: none; }

.status-badge { padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
.status-draft { background-color: #e5e7eb; color: #374151; }
.status-placed { background-color: #cffafe; color: #0891b2; }
.status-shipped { background-color: #dbeafe; color: #1d4ed8; }
.status-received { background-color: #d1fae5; color: #065f46; }
.status-cancelled { background-color: #fee2e2; color: #991b1b; }

/* Drawer */
.form-drawer {
    position: fixed; top: 0; right: 0; width: var(--drawer-width); height: 100%;
    background-color: var(--bg-content); box-shadow: -10px 0 30px rgba(0,0,0,0.1);
    transform: translateX(100%); transition: transform 0.4s ease;
    z-index: 1050; display: flex; flex-direction: column;
}
.form-drawer.is-open { transform: translateX(0); }
.drawer-header { padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
#drawerTitle { font-size: 1.5rem; font-weight: 600; }
.close-drawer-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; transition: all 0.2s ease; }
.close-drawer-btn:hover { background: #e5e7eb; transform: rotate(90deg); }
.drawer-body { padding: 2rem; overflow-y: auto; flex-grow: 1; }
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.form-group { margin-bottom: 1.5rem; }
.drawer-body label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.9rem; }
.drawer-body input, .drawer-body select {
    width: 100%; padding: 12px 14px; border: 1px solid var(--border-color);
    border-radius: 8px; background-color: var(--bg-main);
}
.drawer-body input:focus, .drawer-body select:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }

#ingredients-section { margin-top: 2rem; }
#ingredients-section h4 { font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem; }
.ingredient-adder { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; }
.ingredient-adder .select2-container { flex-grow: 1; }
.ingredient-adder input { width: 120px; }
#poItemsTable th { padding: 0.75rem 1rem; }
#poItemsTable td { padding: 0.5rem 1rem; vertical-align: middle; }
.total-row td { font-weight: 700; font-size: 1.1rem; }

.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 1rem; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2>Purchase Orders</h2>
        <button id="addBtn" class="btn btn-primary">
            <span class="material-icons-outlined">add_shopping_cart</span>
            Create Purchase Order
        </button>
    </div>

    <div class="table-container">
        <div class="table-header">
            <input type="text" id="searchInput" placeholder="Search by supplier...">
            <div class="filter-bar" id="statusFilterBar">
                <button class="filter-btn active" data-status="All">All</button>
                <button class="filter-btn" data-status="Draft">Draft</button>
                <button class="filter-btn" data-status="Placed">Placed</button>
                <button class="filter-btn" data-status="Received">Received</button>
                <button class="filter-btn" data-status="Cancelled">Cancelled</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>PO ID</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="poTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Drawer -->
<aside id="formDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Create Purchase Order</h3>
        <button class="close-drawer-btn" title="Close"><span class="material-icons-outlined">close</span></button>
    </div>
    <form id="poForm" class="drawer-body">
        <input type="hidden" id="poId" name="po_id">
        <div class="form-grid">
            <div class="form-group">
                <label for="supplier">Supplier</label>
                <select id="supplier" name="supplier_id" required style="width:100%;"></select>
            </div>
            <div class="form-group">
                <label for="orderDate">Order Date</label>
                <input type="date" id="orderDate" name="order_date" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="Draft">Draft</option>
                    <option value="Placed">Placed</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Received">Received</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label for="expectedDelivery">Expected Delivery</label>
                <input type="date" id="expectedDelivery" name="expected_delivery_date">
            </div>
            <div class="form-group">
                <label for="actualDelivery">Actual Delivery</label>
                <input type="date" id="actualDelivery" name="actual_delivery_date">
            </div>
        </div>

        <div id="ingredients-section">
            <h4>Order Items</h4>
            <div class="ingredient-adder">
                <select id="ingredient" style="width:100%;"></select>
                <input type="number" id="quantity" placeholder="Quantity" min="0.01" step="0.01">
                <input type="number" id="unitPrice" placeholder="Unit Price" min="0.01" step="0.01">
                <button type="button" id="addIngredientBtn" class="btn btn-primary" style="padding: 10px 15px;">Add</button>
            </div>
            <div class="table-responsive">
                <table id="poItemsTable">
                    <thead><tr><th>Ingredient</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="total-row"><td colspan="3" style="text-align:right;">Total Cost:</td><td id="grandTotal" colspan="2">$0.00</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </form>
    <div class="drawer-footer">
        <button type="button" class="btn close-drawer-btn" style="width:auto;">Cancel</button>
        <button type="submit" form="poForm" class="btn btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Purchase Order
        </button>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Purchase Orders';

    const App = {
        elements: {
            addBtn: $('#addBtn'),
            drawer: $('#formDrawer'),
            closeDrawerBtn: $('.close-drawer-btn'),
            form: $('#poForm'),
            tableBody: $('#poTableBody'),
            drawerTitle: $('#drawerTitle'),
            supplierSelect: $('#supplier'),
            ingredientSelect: $('#ingredient'),
            addIngredientBtn: $('#addIngredientBtn'),
            poItemsTableBody: $('#poItemsTable tbody'),
            grandTotal: $('#grandTotal'),
            statusFilterBar: $('#statusFilterBar'),
        },
        state: {
            purchaseOrders: [],
            suppliers: [],
            ingredients: [],
            currentPoItems: new Map(),
            activeStatusFilter: 'All',
        },

        init() {
            this.initSelect2();
            this.bindEvents();
            this.loadInitialData();
        },

        initSelect2() {
            this.elements.supplierSelect.select2({ placeholder: 'Select a supplier', dropdownParent: this.elements.drawer });
            this.elements.ingredientSelect.select2({ placeholder: 'Select an ingredient', dropdownParent: this.elements.drawer });
        },

        bindEvents() {
            this.elements.addBtn.on('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.on('click', () => this.closeDrawer());
            this.elements.form.on('submit', e => this.handleFormSubmit(e));
            $('#searchInput').on('input', e => this.renderTable());
            this.elements.addIngredientBtn.on('click', () => this.addPoItem());
            this.elements.poItemsTableBody.on('click', '.remove-item-btn', e => {
                this.removePoItem($(e.currentTarget).data('id'));
            });
            this.elements.tableBody.on('click', '.view-btn', e => {
                this.openDrawer($(e.currentTarget).data('id'));
            });
            this.elements.tableBody.on('click', '.delete-btn', e => {
                this.handleDelete($(e.currentTarget).data('id'));
            });
            this.elements.statusFilterBar.on('click', '.filter-btn', e => {
                const button = $(e.currentTarget);
                this.elements.statusFilterBar.find('.active').removeClass('active');
                button.addClass('active');
                this.state.activeStatusFilter = button.data('status');
                this.renderTable();
            });
        },

        async loadInitialData() {
            try {
                const [poRes, supRes, ingRes] = await Promise.all([
                    fetch('ajax/ajax_handler_purchase_orders.php?action=fetchAll'),
                    fetch('ajax/ajax_handler_suppliers.php?action=fetchAll'),
                    fetch('ajax/ajax_handler_ingredients.php?action=fetchAll')
                ]);
                const poData = await poRes.json();
                const supData = await supRes.json();
                const ingData = await ingRes.json();

                if (poData.success) this.state.purchaseOrders = poData.data;
                if (supData.success) {
                    this.state.suppliers = supData.data.filter(s => s.IsActive == 1);
                    this.populateSelect(this.elements.supplierSelect, this.state.suppliers, 'SupplierID', 'SupplierName');
                }
                if (ingData.success) {
                    this.state.ingredients = ingData.data;
                    this.populateSelect(this.elements.ingredientSelect, this.state.ingredients, 'IngredientID', 'Name');
                }
                this.renderTable();
            } catch (error) {
                showToast('Failed to load initial data: ' + error.message, 'error');
            }
        },

        populateSelect(select, data, valField, textField) {
            select.empty().append(new Option('', ''));
            data.forEach(item => select.append(new Option(this.escapeHTML(item[textField]), item[valField])));
            select.trigger('change');
        },

        renderTable() {
            const tbody = this.elements.tableBody;
            const searchTerm = $('#searchInput').val().toLowerCase();
            tbody.empty();

            let filtered = this.state.purchaseOrders;

            // Filter by status
            if (this.state.activeStatusFilter !== 'All') {
                filtered = filtered.filter(po => po.Status === this.state.activeStatusFilter);
            }

            // Filter by search term
            if (searchTerm) {
                filtered = filtered.filter(po =>
                    po.SupplierName.toLowerCase().includes(searchTerm)
                );
            }

            if (filtered.length === 0) {
                tbody.append('<tr><td colspan="6" style="text-align:center;">No purchase orders found.</td></tr>');
                return;
            }

            filtered.forEach(po => {
                const orderDate = new Date(po.OrderDate).toLocaleDateString();
                const deleteButton = po.Status === 'Draft' 
                    ? `<button class="btn btn-danger delete-btn" data-id="${po.PurchaseOrderID}" title="Delete Draft" style="padding: 8px 12px;"><span class="material-icons-outlined">delete_outline</span></button>`
                    : '';

                const row = `
                    <tr>
                        <td>PO-${String(po.PurchaseOrderID).padStart(4, '0')}</td>
                        <td>${this.escapeHTML(po.SupplierName)}</td>
                        <td>${orderDate}</td>
                        <td><span class="status-badge status-${po.Status.toLowerCase()}">${po.Status}</span></td>
                        <td>${parseFloat(po.TotalCost).toFixed(2)}</td>
                        <td style="display:flex; gap: 8px;">
                            <button class="btn view-btn" data-id="${po.PurchaseOrderID}" title="View/Edit" style="padding: 8px 12px;"><span class="material-icons-outlined">visibility</span></button>
                            ${deleteButton}
                        </td>
                    </tr>`;
                tbody.append(row);
            });
        },

        openDrawer(id = null) {
            this.elements.form[0].reset();
            this.elements.supplierSelect.val(null).trigger('change');
            this.state.currentPoItems.clear();
            this.renderPoItems();

            if (id) {
                this.elements.drawerTitle.text('View/Edit Purchase Order');
                this.loadSinglePo(id);
            } else {
                this.elements.drawerTitle.text('Create Purchase Order');
                $('#poId').val('');
                $('#orderDate').val(new Date().toISOString().slice(0, 10));
                $('#status').val('Draft');
            }
            this.elements.drawer.addClass('is-open');
        },

        async loadSinglePo(id) {
            try {
                const response = await fetch(`ajax/ajax_handler_purchase_orders.php?action=fetchSingle&id=${id}`);
                const res = await response.json();
                if (!res.success) throw new Error(res.message);
                
                const { po, items } = res.data;
                $('#poId').val(po.PurchaseOrderID);
                this.elements.supplierSelect.val(po.SupplierID).trigger('change');
                $('#status').val(po.Status);
                $('#orderDate').val(po.OrderDate.split(' ')[0]); // Handle timestamp
                $('#expectedDelivery').val(po.ExpectedDeliveryDate);
                $('#actualDelivery').val(po.ActualDeliveryDate);

                items.forEach(item => {
                    const ingredient = this.state.ingredients.find(i => i.IngredientID == item.IngredientID);
                    this.state.currentPoItems.set(parseInt(item.IngredientID), {
                        name: ingredient ? ingredient.Name : 'Unknown',
                        quantity: parseFloat(item.QuantityOrdered),
                        unitPrice: parseFloat(item.UnitPrice)
                    });
                });
                this.renderPoItems();
            } catch (error) {
                showToast('Error loading PO details: ' + error.message, 'error');
            }
        },

        closeDrawer() {
            this.elements.drawer.removeClass('is-open');
        },

        addPoItem() {
            const ingredientId = parseInt(this.elements.ingredientSelect.val());
            const quantity = parseFloat($('#quantity').val());
            const unitPrice = parseFloat($('#unitPrice').val());

            if (!ingredientId || isNaN(quantity) || isNaN(unitPrice) || quantity <= 0 || unitPrice < 0) {
                showToast('Please select an ingredient and enter valid quantity/price.', 'error');
                return;
            }
            if (this.state.currentPoItems.has(ingredientId)) {
                showToast('Ingredient already in order. Remove to re-add.', 'warning');
                return;
            }
            const ingredient = this.state.ingredients.find(i => i.IngredientID == ingredientId);
            this.state.currentPoItems.set(ingredientId, { name: ingredient.Name, quantity, unitPrice });
            this.renderPoItems();
            
            this.elements.ingredientSelect.val(null).trigger('change');
            $('#quantity').val('');
            $('#unitPrice').val('');
        },

        removePoItem(ingredientId) {
            this.state.currentPoItems.delete(ingredientId);
            this.renderPoItems();
        },

        renderPoItems() {
            const tbody = this.elements.poItemsTableBody;
            tbody.empty();
            let grandTotal = 0;
            if (this.state.currentPoItems.size === 0) {
                tbody.append('<tr><td colspan="5" style="text-align:center;">No items added.</td></tr>');
            } else {
                this.state.currentPoItems.forEach((item, id) => {
                    const subtotal = item.quantity * item.unitPrice;
                    grandTotal += subtotal;
                    const row = `
                        <tr>
                            <td>${this.escapeHTML(item.name)}</td>
                            <td>${item.quantity.toFixed(2)}</td>
                            <td>$${item.unitPrice.toFixed(2)}</td>
                            <td>$${subtotal.toFixed(2)}</td>
                            <td><button type="button" class="btn remove-item-btn" data-id="${id}"><span class="material-icons-outlined">delete</span></button></td>
                        </tr>`;
                    tbody.append(row);
                });
            }
            this.elements.grandTotal.text(`$${grandTotal.toFixed(2)}`);
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form[0]);
            formData.append('action', 'save');
            
            const items = Array.from(this.state.currentPoItems.entries()).map(([id, details]) => ({
                ingredient_id: id,
                quantity: details.quantity,
                unit_price: details.unitPrice
            }));

            if (items.length === 0) {
                showToast('Cannot save an empty purchase order.', 'error');
                return;
            }
            formData.append('items', JSON.stringify(items));

            try {
                const response = await fetch('ajax/ajax_handler_purchase_orders.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (!data.success) throw new Error(data.message);
                
                this.closeDrawer();
                await this.loadInitialData();
                showToast(data.message, 'success');
            } catch (error) {
                showToast('Error saving PO: ' + error.message, 'error');
            }
        },

        async handleDelete(id) {
            if (!confirm('Are you sure you want to delete this draft order? This action cannot be undone.')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('po_id', id);

                const response = await fetch('ajax/ajax_handler_purchase_orders.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (!data.success) throw new Error(data.message);
                
                await this.loadInitialData();
                showToast(data.message, 'success');
            } catch (error) {
                showToast('Error deleting order: ' + error.message, 'error');
            }
        },

        escapeHTML(str) {
            return str ? str.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]) : '';
        }
    };
    
    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>