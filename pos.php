<?php 
session_start();
// For security, it's better to include header files that might contain session checks, etc.
require_once 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Terminal</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Link to the dedicated POS stylesheet -->
    <link rel="stylesheet" href="css/pos_style.css">
</head>
<body>

<div id="pos-wrapper">

    <!-- =================================================================
    // VIEW 1: TABLES VIEW (Default)
    // ================================================================= -->
    <div id="tables-view">
        <header id="tables-view-header">
            <h1>Select a Table</h1>
            <button id="btn-refresh-tables" class="btn-pos btn-secondary" title="Refresh Tables">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </header>
        <main id="tables-view-content">
            <div class="table-section">
                <h2><i class="fas fa-users"></i> Occupied</h2>
                <div id="occupied-tables-grid" class="tables-grid-container">
                    <!-- Occupied table cards will be dynamically inserted here -->
                </div>
            </div>
            <hr class="section-divider">
            <div class="table-section">
                <h2><i class="fas fa-chair"></i> Available</h2>
                <div id="available-tables-grid" class="tables-grid-container">
                    <!-- Available table cards will be dynamically inserted here -->
                </div>
            </div>
        </main>
    </div>

    <!-- =================================================================
    // VIEW 2: ORDER VIEW (Hidden by default)
    // ================================================================= -->
    <div id="order-view" style="display: none;">
        <!-- Left Panel: Live Order Ticket -->
        <aside id="pos-ticket">
            <header id="ticket-header">
                 <button id="btn-back-to-tables" class="btn-pos btn-secondary">
                    <i class="fas fa-arrow-left"></i> All Tables
                </button>
                <div class="active-order-header">
                    <h3>Order #<span id="active-order-id"></span></h3>
                    <p>Table: <span id="active-table-number"></span></p>
                </div>
            </header>
            <div id="ticket-items-container">
                <!-- Items will be dynamically inserted here -->
            </div>
            <footer id="ticket-footer">
                <div class="totals">
                    <div class="total-line">
                        <span>Subtotal</span>
                        <span id="ticket-subtotal">Rs 0.00</span>
                    </div>
                    <div class="total-line grand-total">
                        <span>Total</span>
                        <span id="ticket-total">Rs 0.00</span>
                    </div>
                </div>
                <div class="actions">
                    <button id="btn-cancel-order" class="btn-pos btn-danger">
                        <i class="fas fa-times-circle"></i> Cancel
                    </button>
                    <button id="btn-finalize-order" class="btn-pos btn-success">
                        <i class="fas fa-check-circle"></i> Finalize & Pay
                    </button>
                </div>
            </footer>
        </aside>

        <!-- Right Panel: Menu -->
        <main id="pos-menu">
            <header id="menu-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="menu-search-input" placeholder="Search for food or drinks...">
                </div>
                <div id="menu-categories">
                    <!-- Category tabs will be dynamically inserted here -->
                </div>
            </header>
            <div id="menu-grid">
                <!-- Menu items will be dynamically inserted here -->
            </div>
        </main>
    </div>

</div>

<!-- MODALS (Remain the same) -->
<div id="modal-start-order" class="pos-modal">
    <div class="modal-content">
        <header class="modal-header">
            <h2>Start New Order</h2>
            <button class="btn-close-modal" aria-label="Close modal">&times;</button>
        </header>
        <div class="modal-body">
            <p>Assign a staff member for <strong>Table <span id="modal-table-number"></span></strong></p>
            <div id="modal-staff-grid"></div>
        </div>
    </div>
</div>
<div id="modal-confirmation" class="pos-modal">
    <div class="modal-content" style="max-width: 450px;">
        <header class="modal-header">
            <h2 id="confirmation-title">Confirm Action</h2>
            <button class="btn-close-modal" aria-label="Close modal">&times;</button>
        </header>
        <div class="modal-body"><p id="confirmation-text">Are you sure?</p></div>
        <footer class="modal-footer">
            <button id="btn-confirm-cancel" class="btn-pos btn-secondary">Cancel</button>
            <button id="btn-confirm-action" class="btn-pos btn-primary">Confirm</button>
        </footer>
    </div>
</div>
<div id="pos-toast"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- CONFIG & STATE ---
    const API_URL = 'ajax/ajax_handler_pos.php';
    let state = {
        allMenuItems: [], allCategories: [], allTables: [], allStaff: [],
        activeOrders: {}, currentOrderId: null, selectedTableId: null,
        confirmationCallback: null,
    };

    // --- UI SELECTORS ---
    const ui = {
        views: {
            tables: document.getElementById('tables-view'),
            order: document.getElementById('order-view'),
        },
        ticket: {
            backBtn: document.getElementById('btn-back-to-tables'),
            orderIdSpan: document.getElementById('active-order-id'),
            tableNumberSpan: document.getElementById('active-table-number'),
            itemsContainer: document.getElementById('ticket-items-container'),
            footer: document.getElementById('ticket-footer'),
            subtotal: document.getElementById('ticket-subtotal'),
            total: document.getElementById('ticket-total'),
            cancelBtn: document.getElementById('btn-cancel-order'),
            finalizeBtn: document.getElementById('btn-finalize-order'),
        },
        menu: {
            categories: document.getElementById('menu-categories'),
            searchInput: document.getElementById('menu-search-input'),
            grid: document.getElementById('menu-grid'),
        },
        tables: {
            occupiedGrid: document.getElementById('occupied-tables-grid'),
            availableGrid: document.getElementById('available-tables-grid'),
            refreshBtn: document.getElementById('btn-refresh-tables'),
        },
        modals: {
            startOrder: document.getElementById('modal-start-order'),
            startOrderTableNum: document.getElementById('modal-table-number'),
            staffGrid: document.getElementById('modal-staff-grid'),
            confirmation: document.getElementById('modal-confirmation'),
            confirmTitle: document.getElementById('confirmation-title'),
            confirmText: document.getElementById('confirmation-text'),
            confirmActionBtn: document.getElementById('btn-confirm-action'),
            confirmCancelBtn: document.getElementById('btn-confirm-cancel'),
        },
        toast: document.getElementById('pos-toast'),
    };

    // --- API & DATA HANDLING ---
    async function apiCall(action, options = {}) {
        const { method = 'GET', body = null } = options;
        const url = new URL(API_URL, window.location.href);
        url.searchParams.append('action', action);
        
        const fetchOptions = { method, headers: {} };

        if (method === 'POST') {
            fetchOptions.body = body;
        } else if (body) {
            for (const [key, value] of Object.entries(body)) {
                url.searchParams.append(key, value);
            }
        }

        try {
            const response = await fetch(url.toString(), fetchOptions);
            const responseData = await response.json();
            if (!response.ok || !responseData.success) {
                throw new Error(responseData.message || `HTTP error! Status: ${response.status}`);
            }
            return responseData;
        } catch (error) {
            console.error('API Call Failed:', error);
            showToast(error.message, true);
            throw error;
        }
    }

    async function loadInitialData() {
        try {
            showToast('Loading initial data...', false, true);
            const response = await apiCall('getInitialData');
            const data = response.data;
            state.allMenuItems = data.menu_items;
            state.allCategories = data.categories;
            state.allTables = data.tables;
            state.allStaff = data.staff;
            state.activeOrders = data.active_orders;
            
            renderAll(); // Render the default view first
            
            // Now check if we need to restore a view
            const wasRestored = await checkSessionAndRestore();
            
            if (!wasRestored) {
                showToast('Ready!', false);
            }

        } catch (error) { /* Handled */ }
    }

    async function checkSessionAndRestore() {
        try {
            const response = await apiCall('getPosSession');
            const orderId = response.order_id;
            if (orderId) {
                // Check if the order is still active before restoring
                const tableId = Object.keys(state.activeOrders).find(key => state.activeOrders[key].OrderID == orderId);
                if (!tableId) {
                    // The order is no longer active, so clear the session
                    await apiCall('clearPosSession');
                    return false;
                }

                showToast('Restoring previous session...', false, true);
                const orderDetails = await apiCall('getOrderDetails', { body: { order_id: orderId } });
                renderOrderTicket(orderDetails.data);
                showOrderView();
                showToast('Session restored.', false);
                return true; // Indicate that a session was restored
            }
        } catch (error) {
            console.error("Failed to restore session, starting fresh.", error);
            await apiCall('clearPosSession').catch(e => console.error("Failed to clear bad session", e));
        }
        return false; // No session to restore
    }
    
    async function refreshTables() {
        const refreshBtnIcon = ui.tables.refreshBtn.querySelector('i');
        refreshBtnIcon.classList.add('fa-spin');
        try {
            const response = await apiCall('getInitialData');
            state.allTables = response.data.tables;
            state.activeOrders = response.data.active_orders;
            renderTables();
            showToast('Tables refreshed.');
        } catch (error) { /* Handled */ }
        finally {
            refreshBtnIcon.classList.remove('fa-spin');
        }
    }

    // --- VIEW MANAGEMENT ---
    function showOrderView() {
        ui.views.tables.style.display = 'none';
        ui.views.order.style.display = 'grid'; // Use grid to activate the 2-column layout
    }

    function showTablesView() {
        ui.views.order.style.display = 'none';
        ui.views.tables.style.display = 'flex';
        state.currentOrderId = null;
        state.selectedTableId = null;
    }

    // --- RENDER FUNCTIONS ---
    function renderAll() {
        renderTables();
        renderCategories();
        renderMenu();
        renderStaffModal();
    }

    function renderTables() {
        const occupiedGrid = ui.tables.occupiedGrid;
        const availableGrid = ui.tables.availableGrid;
        occupiedGrid.innerHTML = '';
        availableGrid.innerHTML = '';

        let occupiedCount = 0;
        let availableCount = 0;

        state.allTables.forEach(table => {
            const activeOrder = state.activeOrders[table.TableID];
            const status = activeOrder ? 'occupied' : 'available';
            const tableEl = document.createElement('div');
            tableEl.className = `table-card ${status}`;
            tableEl.dataset.tableId = table.TableID;
            tableEl.dataset.tableNumber = table.TableNumber;

            if (activeOrder) {
                tableEl.dataset.orderId = activeOrder.OrderID;
            }

            tableEl.innerHTML = `
                <i class="fas fa-${status === 'occupied' ? 'users' : 'chair'}"></i>
                <span class="table-number">Table ${table.TableNumber}</span>
                <span class="table-status">${status}</span>
            `;
            tableEl.addEventListener('click', () => handleTableClick(tableEl));
            
            if (status === 'occupied') {
                occupiedGrid.appendChild(tableEl);
                occupiedCount++;
            } else {
                availableGrid.appendChild(tableEl);
                availableCount++;
            }
        });

        if (occupiedCount === 0) {
            occupiedGrid.innerHTML = '<p class="empty-section-message">No occupied tables.</p>';
        }
        if (availableCount === 0) {
            availableGrid.innerHTML = '<p class="empty-section-message">All tables are currently occupied.</p>';
        }
    }

    function renderCategories() {
        ui.menu.categories.innerHTML = '';
        const allButton = document.createElement('button');
        allButton.className = 'category-tab active';
        allButton.dataset.categoryId = 'all';
        allButton.textContent = 'All';
        ui.menu.categories.appendChild(allButton);

        state.allCategories.forEach(cat => {
            const catButton = document.createElement('button');
            catButton.className = 'category-tab';
            catButton.dataset.categoryId = cat.CategoryID;
            catButton.textContent = cat.CategoryName;
            ui.menu.categories.appendChild(catButton);
        });
    }

    function renderMenu(filter = {}) {
        const { categoryId = 'all', searchTerm = '' } = filter;
        ui.menu.grid.innerHTML = '';
        const lowerCaseSearchTerm = searchTerm.toLowerCase();
        const filteredItems = state.allMenuItems.filter(item => 
            (categoryId === 'all' || item.CategoryID == categoryId) && 
            (!searchTerm || item.Name.toLowerCase().includes(lowerCaseSearchTerm))
        );

        if (filteredItems.length === 0) {
            ui.menu.grid.innerHTML = '<p class="empty-grid-message">No items match your criteria.</p>';
            return;
        }

        filteredItems.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'menu-card';
            const imageStyle = item.ImageUrl ? `style="background-image: url('uploads/${item.ImageUrl}')"` : '';
            const icon = item.ImageUrl ? '' : '<i class="fas fa-utensils"></i>';
            itemEl.innerHTML = `
                <div class="menu-card-img" ${imageStyle}>${icon}</div>
                <div class="menu-card-body">
                    <p class="menu-card-name">${item.Name}</p>
                    <p class="menu-card-price">Rs ${parseFloat(item.Price).toFixed(2)}</p>
                </div>`;
            itemEl.addEventListener('click', () => handleMenuItemClick(item.MenuItemID));
            ui.menu.grid.appendChild(itemEl);
        });
    }
    
    function renderStaffModal() {
        ui.modals.staffGrid.innerHTML = '';
        state.allStaff.forEach(staff => {
            const staffEl = document.createElement('div');
            staffEl.className = 'staff-card';
            staffEl.innerHTML = `<i class="fas fa-user-circle"></i><span>${staff.FirstName}</span>`;
            staffEl.addEventListener('click', () => handleStaffSelection(staff.StaffID));
            ui.modals.staffGrid.appendChild(staffEl);
        });
    }

    function renderOrderTicket(orderData) {
        if (!orderData || !orderData.orderInfo) return;
        
        const { orderInfo, items } = orderData;
        state.currentOrderId = orderInfo.OrderID;

        ui.ticket.orderIdSpan.textContent = orderInfo.OrderID;
        const table = state.allTables.find(t => t.TableID == orderInfo.TableID);
        ui.ticket.tableNumberSpan.textContent = table ? table.TableNumber : '?';

        ui.ticket.itemsContainer.innerHTML = '';
        if (items && items.length > 0) {
            items.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'ticket-item';
                itemEl.innerHTML = `
                    <div class="item-info">
                        <p class="item-name">${item.Name}</p>
                        <p class="item-price">Rs ${(parseFloat(item.Subtotal) / item.Quantity).toFixed(2)}</p>
                    </div>
                    <div class="item-controls">
                        <button class="btn-qty minus" data-detail-id="${item.OrderDetailID}" data-new-qty="${item.Quantity - 1}" aria-label="Decrease">-</button>
                        <span class="item-qty">${item.Quantity}</span>
                        <button class="btn-qty plus" data-detail-id="${item.OrderDetailID}" data-new-qty="${item.Quantity + 1}" aria-label="Increase">+</button>
                    </div>
                    <div class="item-subtotal">Rs ${parseFloat(item.Subtotal).toFixed(2)}</div>`;
                ui.ticket.itemsContainer.appendChild(itemEl);
            });
        } else {
            ui.ticket.itemsContainer.innerHTML = '<p class="empty-ticket-message">Add items from the menu.</p>';
        }

        const total = parseFloat(orderInfo.TotalAmount);
        ui.ticket.subtotal.textContent = `Rs ${total.toFixed(2)}`;
        ui.ticket.total.textContent = `Rs ${total.toFixed(2)}`;
        ui.ticket.footer.style.display = '';
    }

    // --- EVENT HANDLERS ---
    function setupEventListeners() {
        ui.tables.refreshBtn.addEventListener('click', refreshTables);
        
        ui.ticket.backBtn.addEventListener('click', async () => {
            try {
                await apiCall('clearPosSession');
                showTablesView();
            } catch (error) {
                console.error('Failed to clear session, but navigating back anyway.', error);
                showTablesView();
            }
        });

        ui.menu.categories.addEventListener('click', (e) => {
            if (e.target.classList.contains('category-tab')) handleCategoryClick(e.target);
        });
        ui.menu.searchInput.addEventListener('input', handleMenuSearch);

        ui.ticket.itemsContainer.addEventListener('click', (e) => {
            const qtyBtn = e.target.closest('.btn-qty');
            if (qtyBtn) handleQuantityChange(qtyBtn);
        });
        ui.ticket.cancelBtn.addEventListener('click', () => handleOrderStatusUpdate('Cancelled'));
        ui.ticket.finalizeBtn.addEventListener('click', () => handleOrderStatusUpdate('Completed'));

        document.querySelectorAll('.pos-modal .btn-close-modal').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('.pos-modal').style.display = 'none');
        });
        
        ui.modals.confirmActionBtn.addEventListener('click', () => {
            if (typeof state.confirmationCallback === 'function') state.confirmationCallback();
            closeConfirmationModal();
        });
        ui.modals.confirmCancelBtn.addEventListener('click', closeConfirmationModal);
    }

    async function handleTableClick(tableCard) {
        const { tableId, tableNumber, orderId } = tableCard.dataset;
        state.selectedTableId = tableId;

        if (tableCard.classList.contains('available')) {
            ui.modals.startOrderTableNum.textContent = tableNumber;
            openModal(ui.modals.startOrder);
        } else {
            try {
                // Set session before loading the order
                await apiCall('setPosSession', { 
                    method: 'POST', 
                    body: new URLSearchParams({ order_id: orderId }) 
                });

                const response = await apiCall('getOrderDetails', { body: { order_id: orderId } });
                renderOrderTicket(response.data);
                showOrderView();
            } catch (error) { /* Handled */ }
        }
    }

    async function handleStaffSelection(staffId) {
        closeModal(ui.modals.startOrder);
        try {
            const params = new URLSearchParams({ table_id: state.selectedTableId, staff_id: staffId });
            const response = await apiCall('createOrder', { method: 'POST', body: params });
            
            showToast(response.message);
            await refreshTables();
            
            const newOrderId = response.order_id;
            // Set session for the newly created order
            await apiCall('setPosSession', { 
                method: 'POST', 
                body: new URLSearchParams({ order_id: newOrderId }) 
            });

            const orderDetails = await apiCall('getOrderDetails', { body: { order_id: newOrderId } });
            renderOrderTicket(orderDetails.data);
            showOrderView();

        } catch (error) { /* Handled */ }
    }

    function handleCategoryClick(categoryButton) {
        document.querySelector('#menu-categories .active')?.classList.remove('active');
        categoryButton.classList.add('active');
        renderMenu({ categoryId: categoryButton.dataset.categoryId, searchTerm: ui.menu.searchInput.value });
    }

    function handleMenuSearch() {
        const activeCat = document.querySelector('#menu-categories .active').dataset.categoryId;
        renderMenu({ categoryId: activeCat, searchTerm: ui.menu.searchInput.value });
    }

    async function handleMenuItemClick(menuItemId) {
        try {
            const params = new URLSearchParams({ order_id: state.currentOrderId, menu_item_id: menuItemId, quantity: 1 });
            const response = await apiCall('addItemToOrder', { method: 'POST', body: params });
            renderOrderTicket(response.data);
            showToast(`${response.data.item_name} added.`, false);
        } catch (error) { /* Handled */ }
    }

    async function handleQuantityChange(qtyBtn) {
        const { detailId, newQty } = qtyBtn.dataset;
        try {
            const params = new URLSearchParams({ order_detail_id: detailId, new_quantity: newQty });
            const response = await apiCall('updateItemQuantity', { method: 'POST', body: params });
            renderOrderTicket(response.data);
        } catch (error) { /* Handled */ }
    }

    function handleOrderStatusUpdate(status) {
        const isCompleting = status === 'Completed';
        const title = isCompleting ? 'Finalize & Pay' : 'Cancel Order';
        const text = isCompleting ? 'This will finalize the order and proceed to the payment screen. Continue?' : 'Are you sure you want to cancel this order? This action cannot be undone.';
        const confirmClass = isCompleting ? 'btn-success' : 'btn-danger';

        showConfirmationModal(title, text, confirmClass, async () => {
            try {
                const params = new URLSearchParams({ order_id: state.currentOrderId, status });
                const response = await apiCall('updateOrderStatus', { method: 'POST', body: params });

                // Always clear the POS view session when leaving the order screen
                await apiCall('clearPosSession');

                if (isCompleting) {
                    // Redirect to payment page on success
                    showToast('Order finalized. Redirecting to payment...');
                    window.location.href = `payments.php?order_id=${state.currentOrderId}`;
                } else {
                    // For cancellation, just go back to the table view
                    showToast(response.message);
                    showTablesView();
                    await refreshTables();
                }
            } catch (error) {
                // Error is already shown by apiCall, just log it for debugging.
                console.error(`Failed to update order status to ${status}:`, error);
            }
        });
    }

    // --- HELPERS ---
    function openModal(modalElement) { modalElement.style.display = 'flex'; }
    function closeModal(modalElement) { modalElement.style.display = 'none'; }
    function showToast(message, isError = false, isSticky = false) {
        ui.toast.textContent = message;
        ui.toast.className = `show ${isError ? 'error' : 'success'}`;
        if (!isSticky) setTimeout(() => { ui.toast.className = ''; }, 3000);
    }
    function showConfirmationModal(title, text, confirmClass, onConfirm) {
        ui.modals.confirmTitle.textContent = title;
        ui.modals.confirmText.textContent = text;
        ui.modals.confirmActionBtn.className = `btn-pos ${confirmClass}`;
        state.confirmationCallback = onConfirm;
        openModal(ui.modals.confirmation);
    }
    function closeConfirmationModal() {
        state.confirmationCallback = null;
        closeModal(ui.modals.confirmation);
    }

    // --- INITIALIZATION ---
    function init() {
        setupEventListeners();
        loadInitialData();
    }

    init();
});
</script>

</body>
</html>
