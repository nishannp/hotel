<?php 
// pos.php - The Ultimate Visual POS Experience
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Visual POS Terminal';</script>";
?>

<div class="pos-container" style="gap: 0; background-color: #f4f6f9;">
    
    <!-- Left Panel: The Live Order Ticket -->
    <aside class="pos-sidebar" style="width: 350px;">
        <div class="pos-sidebar-header" id="ticketHeader">No Active Order</div>
        <div class="pos-main-body" style="padding: 15px;">
            <div id="ticketWelcomeView">
                <p style="text-align: center; color: #777; margin-top: 50px;">
                    <i class="fas fa-tablet-alt" style="font-size: 3rem; color: #ddd;"></i><br><br>
                    Select a table and an order to begin.
                </p>
            </div>
            <div id="ticketOrderView" style="display: none;">
                <div class="order-ticket-items" id="orderItemsList" style="max-height: calc(100vh - 380px);"></div>
                <div class="order-ticket-total" id="orderTotal">Total: $0.00</div>
                <div class="pos-actions">
                    <button type="button" id="cancelOrderBtn" style="background-color: #c0392b;">Cancel</button>
                    <button type="button" id="completeOrderBtn" style="background-color: #27ae60;">Complete</button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Center Panel: The Visual Menu -->
    <main class="pos-main">
        <div class="pos-category-tabs" id="categoryTabs"></div>
        <div class="pos-menu-grid" id="menuGrid"></div>
    </main>

    <!-- Right Panel: Tables & Staff -->
    <aside class="pos-sidebar" style="width: 250px;">
        <div class="pos-sidebar-header">
            Tables <button id="refreshBtn" style="float: right; font-size: 0.8rem; padding: 5px 10px;">↻</button>
        </div>
        <div id="tablesGrid" class="pos-sidebar-body" style="grid-template-columns: 1fr 1fr;"></div>
    </aside>

</div>

<!-- Modal for Starting a New Order with Visual Staff Selection -->
<div id="startOrderModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 id="startOrderModalTitle">Start Order</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <p>Please select the staff member creating this order.</p>
            <input type="hidden" id="startOrderTableId">
            <div class="staff-selector-grid" id="staffSelector"></div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // --- Globals & Configuration ---
    let allMenuItems = [];
    let currentOrderId = null;
    const actionPosAjaxUrl = 'ajax/ajax_handler_pos.php'; // For actions

    // --- Main UI Elements ---
    const tablesGrid = $('#tablesGrid');
    const categoryTabs = $('#categoryTabs');
    const menuGrid = $('#menuGrid');
    const ticketHeader = $('#ticketHeader');
    const orderItemsList = $('#orderItemsList');
    const orderTotal = $('#orderTotal');
    const ticketWelcomeView = $('#ticketWelcomeView');
    const ticketOrderView = $('#ticketOrderView');
    const startOrderModal = $('#startOrderModal');

    // --- Core Functions ---
    function loadInitialData() {
        // 1. Load Tables
        loadTables();
        
        // 2. Load Menu Data (Categories & Items)
        $.getJSON('ajax/ajax_handler_pos_menu.php', function(data) {
            if (data.success) {
                allMenuItems = data.menu_items;
                // Populate category tabs
                categoryTabs.empty();
                categoryTabs.append('<div class="pos-category-tab active" data-category-id="all">All Items</div>');
                data.categories.forEach(cat => {
                    categoryTabs.append(`<div class="pos-category-tab" data-category-id="${cat.CategoryID}">${cat.CategoryName}</div>`);
                });
                // Display all items initially
                filterMenu('all');
            }
        });
        
        // 3. Load Staff for the modal
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
            }
        });
    }

    function loadTables() {
        $.getJSON('ajax/ajax_handler_pos_simple.php', function(data) {
            if (!data.success) return;
            tablesGrid.empty();
            data.tables.forEach(table => {
                const activeOrder = data.active_orders[table.TableID];
                const card = $('<div></div>')
                    .addClass('table-card-sm').addClass(activeOrder ? 'occupied' : 'available')
                    .data({ tableId: table.TableID, tableNumber: table.TableNumber })
                    .html(`<i class="fas fa-chair"></i><br>Table ${table.TableNumber}`);
                if (activeOrder) card.data('orderId', activeOrder.OrderID);
                tablesGrid.append(card);
            });
        });
    }

    function filterMenu(categoryId) {
        menuGrid.empty();
        const itemsToDisplay = (categoryId === 'all')
            ? allMenuItems
            : allMenuItems.filter(item => item.CategoryID == categoryId);
        
        itemsToDisplay.forEach(item => {
            const imageStyle = item.ImageUrl ? `background-image: url('${item.ImageUrl}')` : '';
            const itemCard = $(`
                <div class="menu-item-card" data-menu-item-id="${item.MenuItemID}">
                    <div class="item-image" style="${imageStyle}">${!item.ImageUrl ? '<i class="fas fa-utensils"></i>' : ''}</div>
                    <div class="item-info">
                        <div class="item-name">${item.Name}</div>
                        <div class="item-price">$${parseFloat(item.Price).toFixed(2)}</div>
                    </div>
                </div>
            `);
            menuGrid.append(itemCard);
        });
    }

    function renderOrderTicket(orderId) {
        currentOrderId = orderId;
        $.getJSON(`${actionPosAjaxUrl}?action=getOrderDetails&order_id=${orderId}`, function(data) {
            if (data.success) {
                const order = data.data.orderInfo;
                const items = data.data.items;
                ticketHeader.text(`Order #${order.OrderID} (Table ${order.TableID})`);
                orderTotal.text(`Total: $${parseFloat(order.TotalAmount).toFixed(2)}`);
                
                orderItemsList.empty();
                if (items.length > 0) {
                    items.forEach(item => {
                        orderItemsList.append(`
                            <div style="display:flex; justify-content:space-between; align-items:center; padding: 10px 5px; border-bottom: 1px dashed #eee;">
                                <div>${item.Name}</div>
                                <div>
                                    <span class="qty-btn" data-detail-id="${item.OrderDetailID}" data-qty="${item.Quantity}" data-change="-1">-</span>
                                    <b>${item.Quantity}</b>
                                    <span class="qty-btn" data-detail-id="${item.OrderDetailID}" data-qty="${item.Quantity}" data-change="1">+</span>
                                    <span style="display:inline-block; width: 80px; text-align:right;">$${parseFloat(item.Subtotal).toFixed(2)}</span>
                                </div>
                            </div>`);
                    });
                } else {
                    orderItemsList.html('<p style="text-align:center; color:#888; padding:20px;">Click an item to add it to the order.</p>');
                }
                ticketWelcomeView.hide();
                ticketOrderView.show();
            }
        });
    }

    function resetTicket() {
        currentOrderId = null;
        ticketHeader.text('No Active Order');
        ticketWelcomeView.show();
        ticketOrderView.hide();
    }

    // --- Event Handlers ---
    $('#refreshBtn').on('click', loadTables);
    
    categoryTabs.on('click', '.pos-category-tab', function() {
        $('.pos-category-tab').removeClass('active');
        $(this).addClass('active');
        filterMenu($(this).data('categoryId'));
    });
    
    tablesGrid.on('click', '.table-card-sm', function() {
        const card = $(this);
        $('.table-card-sm').removeClass('selected');
        card.addClass('selected');
        if (card.hasClass('available')) {
            $('#startOrderModalTitle').text(`Start Order for Table ${card.data('tableNumber')}`);
            $('#startOrderTableId').val(card.data('tableId'));
            $('.staff-card').removeClass('selected');
            startOrderModal.show();
        } else {
            renderOrderTicket(card.data('orderId'));
        }
    });
    
    startOrderModal.on('click', '.staff-card', function() {
        const staffId = $(this).data('staffId');
        const tableId = $('#startOrderTableId').val();
        
        $.post(actionPosAjaxUrl, { action: 'createOrder', table_id: tableId, staff_id: staffId }, function(data) {
            if (data.success) {
                startOrderModal.hide();
                loadTables();
                renderOrderTicket(data.order_id);
            } else { alert(data.message); }
        }, 'json');
    });

    menuGrid.on('click', '.menu-item-card', function() {
        if (!currentOrderId) {
            alert("Please select a table and start an order first.");
            return;
        }
        $.post(actionPosAjaxUrl, {
            action: 'addItemToOrder',
            order_id: currentOrderId,
            menu_item_id: $(this).data('menuItemId'),
            quantity: 1
        }, function(data) {
            if (!data.success) alert("Error: " + data.message);
            renderOrderTicket(currentOrderId);
        }, 'json');
    });
    
    orderItemsList.on('click', '.qty-btn', function() {
        const detailId = $(this).data('detailId');
        const newQty = $(this).data('qty') + $(this).data('change');
        
        $.post(actionPosAjaxUrl, { action: 'updateItemQuantity', order_detail_id: detailId, new_quantity: newQty }, function(data) {
            if (!data.success) alert("Error: " + data.message);
            renderOrderTicket(currentOrderId);
        }, 'json');
    });

    $('#ticketOrderView').on('click', '#completeOrderBtn, #cancelOrderBtn', function() {
        const status = $(this).attr('id') === 'completeOrderBtn' ? 'Completed' : 'Cancelled';
        if (confirm(`Are you sure you want to mark this order as ${status}?`)) {
            $.post(actionPosAjaxUrl, { action: 'updateOrderStatus', order_id: currentOrderId, status: status }, function() {
                loadTables();
                resetTicket();
            }, 'json');
        }
    });

    startOrderModal.on('click', '.close-button', () => startOrderModal.hide());

    // --- Initial Load ---
    loadInitialData();
});
</script>