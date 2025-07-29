<?php 
// pos.php - The Ultimate Visual POS Experience
require_once 'includes/header.php'; 
// It's better to set the title via a variable passed to the header
// but for now, this JS is fine.
echo "<script>document.querySelector('.content-header h1').textContent = 'Visual POS Terminal';</script>";
?>

<!-- This is the main container for the POS view -->
<div class="pos-container">
    
    <!-- Left Panel: The Live Order Ticket -->
    <aside id="posOrderTicket" class="pos-sidebar">
        <div class="pos-sidebar-header" id="ticketHeader">No Active Order</div>
        <div class="pos-sidebar-body">
            <div id="ticketWelcomeView">
                <i class="fas fa-receipt"></i>
                <p>Select a table to view or start an order.</p>
            </div>
            <div id="ticketOrderView" style="display: none;">
                <div class="order-ticket-items" id="orderItemsList">
                    <!-- JS will populate this -->
                </div>
                <div class="order-ticket-total">
                    <span>Total</span>
                    <span id="orderTotal">$0.00</span>
                </div>
                <div class="pos-actions">
                    <button type="button" id="cancelOrderBtn">Cancel Order</button>
                    <button type="button" id="completeOrderBtn">Finalize & Pay</button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Center Panel: The Visual Menu -->
    <main id="posMenu" class="pos-main">
        <div class="pos-category-tabs" id="categoryTabs">
            <!-- JS will populate categories -->
        </div>
        <div class="pos-menu-grid" id="menuGrid">
            <!-- JS will populate menu items -->
        </div>
    </main>

    <!-- Right Panel: Tables & Staff -->
    <aside id="posTablesSidebar" class="pos-sidebar">
        <div class="pos-sidebar-header">
            <span>Tables</span>
            <button id="refreshBtn" title="Refresh Tables">↻</button>
        </div>
        <div id="tablesGrid" class="pos-sidebar-body">
            <!-- JS will populate tables -->
        </div>
    </aside>

</div>

<!-- Modal for Starting a New Order with Visual Staff Selection -->
<div id="startOrderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="startOrderModalTitle">Start New Order</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <p>Select the staff member creating this order for <strong id="modalTableName"></strong>.</p>
            <input type="hidden" id="startOrderTableId">
            <div class="staff-selector-grid" id="staffSelector">
                <!-- JS will populate staff -->
            </div>
        </div>
    </div>
</div>

<!-- Generic Confirmation Modal -->
<div id="confirmationModal" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h2 id="confirmationModalTitle">Confirm Action</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <p id="confirmationModalText"></p>
        </div>
        <div class="modal-footer">
            <button id="cancelActionBtn" class="btn-cancel">Cancel</button>
            <button id="confirmActionBtn" class="btn-confirm">Confirm</button>
        </div>
    </div>
</div>

<!-- Toast Notification Placeholder -->
<div id="toastNotification" class="toast-notification"></div>

<script>
$(document).ready(function() {
    // --- Globals & Configuration ---
    let allMenuItems = [];
    let currentOrderId = null;
    const actionPosAjaxUrl = 'ajax/ajax_handler_pos.php';

    // --- UI Elements ---
    const tablesGrid = $('#tablesGrid');
    const categoryTabs = $('#categoryTabs');
    const menuGrid = $('#menuGrid');
    const ticketHeader = $('#ticketHeader');
    const orderItemsList = $('#orderItemsList');
    const orderTotal = $('#orderTotal');
    const ticketWelcomeView = $('#ticketWelcomeView');
    const ticketOrderView = $('#ticketOrderView');
    const startOrderModal = $('#startOrderModal');
    const confirmationModal = $('#confirmationModal');

    // --- Core Functions ---

    // Function to show toast notifications
    function showToast(message, isError = false) {
        const toast = $('#toastNotification');
        toast.text(message).removeClass('error').toggleClass('error', isError);
        toast.addClass('show');
        setTimeout(() => toast.removeClass('show'), 3000);
    }

    // --- NEW: Confirmation Modal Logic ---
    let confirmCallback = null;
    function showConfirmationModal(title, text, confirmBtnClass, onConfirm) {
        $('#confirmationModalTitle').text(title);
        $('#confirmationModalText').text(text);
        
        const confirmBtn = $('#confirmActionBtn');
        confirmBtn.removeClass('btn-confirm delete-confirm-btn').addClass(confirmBtnClass);
        
        confirmCallback = onConfirm; // Store the callback
        
        confirmationModal.show();
    }

    confirmationModal.on('click', '#confirmActionBtn', function() {
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
        confirmationModal.hide();
    });

    confirmationModal.on('click', '#cancelActionBtn, .close-button', function() {
        confirmationModal.hide();
    });
    // --- END: Confirmation Modal Logic ---

    function loadInitialData() {
        loadTables();
        
        $.getJSON('ajax/ajax_handler_pos_menu.php', function(data) {
            if (data.success) {
                allMenuItems = data.menu_items;
                categoryTabs.empty().append('<div class="pos-category-tab active" data-category-id="all">All Items</div>');
                data.categories.forEach(cat => {
                    categoryTabs.append(`<div class="pos-category-tab" data-category-id="${cat.CategoryID}">${cat.CategoryName}</div>`);
                });
                filterMenu('all');
            } else {
                showToast('Failed to load menu.', true);
            }
        }).fail(() => showToast('Error communicating with server for menu.', true));
        
        $.getJSON('ajax/ajax_handler_staff.php?action=fetchAll', function(data) {
            if (data.success) {
                const staffGrid = $('#staffSelector').empty();
                data.data.filter(s => s.IsActive == 1).forEach(s => {
                    staffGrid.append(`
                        <div class="staff-card" data-staff-id="${s.StaffID}">
                            <i class="fas fa-user-circle"></i>
                            <div class="staff-name">${s.FirstName}</div>
                        </div>`);
                });
            } else {
                showToast('Failed to load staff.', true);
            }
        }).fail(() => showToast('Error communicating with server for staff.', true));
    }

    function loadTables() {
        $.getJSON('ajax/ajax_handler_pos_simple.php', function(data) {
            if (!data.success) {
                showToast('Could not refresh tables.', true);
                return;
            }
            tablesGrid.empty();
            data.tables.forEach(table => {
                const activeOrder = data.active_orders[table.TableID];
                const status = activeOrder ? 'Occupied' : 'Available';
                const card = $('<div></div>')
                    .addClass('table-card-sm')
                    .addClass(activeOrder ? 'occupied' : 'available')
                    .data({ tableId: table.TableID, tableNumber: table.TableNumber })
                    .html(`
                        <span class="status-badge">${status}</span>
                        <i class="fas fa-chair"></i>
                        Table ${table.TableNumber}
                    `);
                if (activeOrder) {
                    card.data('orderId', activeOrder.OrderID);
                }
                tablesGrid.append(card);
            });
        }).fail(() => showToast('Error communicating with server for tables.', true));
    }

    function filterMenu(categoryId) {
        menuGrid.empty();
        const itemsToDisplay = (categoryId === 'all')
            ? allMenuItems
            : allMenuItems.filter(item => item.CategoryID == categoryId);
        
        if (itemsToDisplay.length === 0 && categoryId !== 'all') {
            menuGrid.html('<p style="text-align:center; color:#888; padding:40px;">No items in this category.</p>');
            return;
        }

        itemsToDisplay.forEach(item => {
            const imageStyle = item.ImageUrl ? `background-image: url('uploads/${item.ImageUrl}')` : '';
            const imageContent = !item.ImageUrl ? '<i class="fas fa-utensils"></i>' : '';
            const itemCard = $(`
                <div class="menu-item-card" data-menu-item-id="${item.MenuItemID}">
                    <div class="item-image" style="${imageStyle}">${imageContent}</div>
                    <div class="item-info">
                        <div class="item-name" title="${item.Name}">${item.Name}</div>
                        <div class="item-price">${parseFloat(item.Price).toFixed(2)}</div>
                    </div>
                </div>
            `);
            menuGrid.append(itemCard);
        });
    }

    function renderOrderTicket(orderId) {
        if (!orderId) {
            resetTicket();
            return;
        }
        currentOrderId = orderId;
        $.getJSON(`${actionPosAjaxUrl}?action=getOrderDetails&order_id=${orderId}`, function(data) {
            if (data.success) {
                const order = data.data.orderInfo;
                const items = data.data.items;
                
                // Highlight the correct table
                $('.table-card-sm').removeClass('selected');
                $(`.table-card-sm[data-table-id='${order.TableID}']`).addClass('selected');

                ticketHeader.text(`Order #${order.OrderID} | Table ${order.TableID}`);
                orderTotal.text(`${parseFloat(order.TotalAmount).toFixed(2)}`);
                
                orderItemsList.empty();
                if (items.length > 0) {
                    items.forEach(item => {
                        orderItemsList.append(`
                            <div class="order-ticket-item">
                                <div class="item-name">${item.Name}</div>
                                <div class="item-controls">
                                    <span class="qty-btn" data-detail-id="${item.OrderDetailID}" data-qty="${item.Quantity}" data-change="-1">−</span>
                                    <b class="item-qty">${item.Quantity}</b>
                                    <span class="qty-btn" data-detail-id="${item.OrderDetailID}" data-qty="${item.Quantity}" data-change="1">+</span>
                                    <span class="item-subtotal">${parseFloat(item.Subtotal).toFixed(2)}</span>
                                </div>
                            </div>`);
                    });
                } else {
                    orderItemsList.html('<p style="text-align:center; color:#888; padding:20px;">Click an item from the menu to add it to this order.</p>');
                }
                ticketWelcomeView.hide();
                ticketOrderView.show();
            } else {
                showToast(data.message || 'Failed to load order details.', true);
                resetTicket();
            }
        }).fail(() => {
            showToast('Error communicating with server for order details.', true);
            resetTicket();
        });
    }

    function resetTicket() {
        currentOrderId = null;
        $('.table-card-sm').removeClass('selected');
        ticketHeader.text('No Active Order');
        ticketWelcomeView.show();
        ticketOrderView.hide();
    }

    // --- Event Handlers ---
    $('#refreshBtn').on('click', function() {
        showToast('Refreshing tables...');
        loadTables();
    });
    
    categoryTabs.on('click', '.pos-category-tab', function() {
        $('.pos-category-tab').removeClass('active');
        $(this).addClass('active');
        filterMenu($(this).data('categoryId'));
    });
    
    tablesGrid.on('click', '.table-card-sm', function() {
        const card = $(this);
        if (card.hasClass('selected')) {
            resetTicket();
            return;
        }
        
        $('.table-card-sm').removeClass('selected');
        card.addClass('selected');

        if (card.hasClass('available')) {
            $('#modalTableName').text(`Table ${card.data('tableNumber')}`);
            $('#startOrderTableId').val(card.data('tableId'));
            $('.staff-card').removeClass('selected');
            startOrderModal.show();
        } else {
            renderOrderTicket(card.data('orderId'));
        }
    });
    
    startOrderModal.on('click', '.staff-card', function() {
        const staffCard = $(this);
        staffCard.addClass('selected'); // Visual feedback
        const staffId = staffCard.data('staffId');
        const tableId = $('#startOrderTableId').val();
        
        // Prevent multiple clicks
        staffCard.parent().find('.staff-card').css('pointer-events', 'none');

        $.post(actionPosAjaxUrl, { action: 'createOrder', table_id: tableId, staff_id: staffId }, function(data) {
            if (data.success) {
                showToast(`Order #${data.order_id} created!`);
                startOrderModal.hide();
                loadTables(); // This will show the table as occupied
                renderOrderTicket(data.order_id);
            } else {
                showToast(data.message || 'Failed to create order.', true);
            }
        }, 'json').fail(() => showToast('Error communicating with server.', true))
        .always(() => staffCard.parent().find('.staff-card').css('pointer-events', 'auto'));
    });

    menuGrid.on('click', '.menu-item-card', function() {
        if (!currentOrderId) {
            showToast("Please select an active order first.", true);
            return;
        }
        $.post(actionPosAjaxUrl, {
            action: 'addItemToOrder',
            order_id: currentOrderId,
            menu_item_id: $(this).data('menuItemId'),
            quantity: 1
        }, function(data) {
            if (!data.success) {
                showToast(data.message || 'Error adding item.', true);
            }
            // Always re-render to show updated state or error message from trigger
            renderOrderTicket(currentOrderId);
        }, 'json').fail(() => showToast('Error communicating with server.', true));
    });
    
    orderItemsList.on('click', '.qty-btn', function() {
        const detailId = $(this).data('detailId');
        const newQty = $(this).data('qty') + $(this).data('change');
        
        $.post(actionPosAjaxUrl, { action: 'updateItemQuantity', order_detail_id: detailId, new_quantity: newQty }, function(data) {
            if (!data.success) {
                showToast(data.message || 'Error updating quantity.', true);
            }
            renderOrderTicket(currentOrderId);
        }, 'json').fail(() => showToast('Error communicating with server.', true));
    });

    $('#ticketOrderView').on('click', '#completeOrderBtn, #cancelOrderBtn', function() {
        const isCompleting = $(this).attr('id') === 'completeOrderBtn';
        
        if (isCompleting) {
            showConfirmationModal(
                'Finalize & Pay',
                'This will mark the order as "Completed" and take you to the payment screen. Proceed?',
                'btn-confirm',
                function() { // onConfirm
                    $.post(actionPosAjaxUrl, { action: 'updateOrderStatus', order_id: currentOrderId, status: 'Completed' }, function(data) {
                        if (data.success) {
                            // Redirect to payments page with order_id
                            window.location.href = `payments.php?order_id=${currentOrderId}`;
                        } else {
                            showToast(data.message || 'Failed to update order status.', true);
                        }
                    }, 'json').fail(() => showToast('Error communicating with server.', true));
                }
            );
        } else { // Is Cancelling
            showConfirmationModal(
                'Cancel Order',
                'Are you sure you want to cancel this entire order? This action cannot be undone.',
                'delete-confirm-btn',
                function() { // onConfirm
                    $.post(actionPosAjaxUrl, { action: 'updateOrderStatus', order_id: currentOrderId, status: 'Cancelled' }, function(data) {
                        if (data.success) {
                            showToast(`Order marked as Cancelled.`);
                            loadTables();
                            resetTicket();
                        } else {
                            showToast(data.message || 'Failed to update order status.', true);
                        }
                    }, 'json').fail(() => showToast('Error communicating with server.', true));
                }
            );
        }
    });

    startOrderModal.on('click', '.close-button', () => startOrderModal.hide());
    $(document).on('keydown', function(event) {
        if (event.key === "Escape") {
            startOrderModal.hide();
            confirmationModal.hide();
        }
    });

    // --- Initial Load ---
    loadInitialData();
});
</script>

