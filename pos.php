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
    <title>POS Terminal - Party System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<style>
    /*
 * css/pos_style.css
 * Stylesheet for the POS interface with party management.
 * Version: 5.0 - Party System
 */

/* --- VARIABLES & GLOBAL SETUP --- */
:root {
    --pos-bg-primary: #f8f9fa;
    --pos-bg-secondary: #ffffff;
    --pos-bg-tertiary: #f1f3f5;
    --pos-border-color: #dee2e6;
    --pos-text-primary: #212529;
    --pos-text-secondary: #495057;
    --pos-text-muted: #868e96;
    
    --pos-accent-primary: #007bff;
    --pos-accent-primary-hover: #0056b3;
    --pos-accent-success: #28a745;
    --pos-accent-danger: #dc3545;
    
    --pos-status-available: #e9f7ef;
    --pos-status-available-text: #1d6a43;
    --pos-status-partially-occupied: #fff8e1;
    --pos-status-partially-occupied-text: #b45309;
    --pos-status-full: #feecf0;
    --pos-status-full-text: #b91c1c;


    --pos-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    --pos-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --pos-border-radius: 12px;
    --pos-border-radius-sm: 8px;
    --pos-font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

body {
    margin-top: -130px;
    background-color: var(--pos-bg-primary);
    font-family: var(--pos-font-family);
    color: var(--pos-text-primary);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

#pos-wrapper {
    height: 100vh;
    width: 100%;
    overflow: hidden;
}

/* --- GENERAL COMPONENT STYLES --- */
.btn-pos {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 18px;
    border: 1px solid transparent;
    border-radius: var(--pos-border-radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    user-select: none;
}
.btn-pos:hover { transform: translateY(-2px); box-shadow: var(--pos-shadow-sm); }
.btn-pos:active { transform: translateY(0); box-shadow: none; }
.btn-pos.btn-primary { background-color: var(--pos-accent-primary); color: white; }
.btn-pos.btn-primary:hover { background-color: var(--pos-accent-primary-hover); }
.btn-pos.btn-secondary { background-color: var(--pos-bg-secondary); color: var(--pos-text-secondary); border-color: var(--pos-border-color); }
.btn-pos.btn-secondary:hover { background-color: var(--pos-bg-tertiary); border-color: #adb5bd; }
.btn-pos.btn-success { background-color: var(--pos-accent-success); color: white; }
.btn-pos.btn-danger { background-color: var(--pos-accent-danger); color: white; }
.btn-pos.btn-full-width { width: 100%; }

/* --- VIEW 1: TABLES VIEW --- */
#tables-view {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 1.5rem;
    box-sizing: border-box;
    /* Add a subtle background to make the glass effect visible */
    background-color: #e9ecef;
    background-image: linear-gradient(to top right, #f8f9fa, #e9ecef);
}
#tables-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-shrink: 0;
}
#tables-view-header h1 {
    margin: 0;
    font-size: 2rem;
}

#tables-grid-container {
    flex-grow: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    padding: 10px;
}

.tables-section {
    display: flex;
    flex-direction: column;
}

.tables-section-header {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--pos-text-secondary);
    margin: 0 0 1.25rem 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.tables-section-header i {
    color: var(--pos-text-muted);
}

.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.75rem;
}

.empty-section-message {
    color: var(--pos-text-muted);
    padding: 30px;
    text-align: center;
    grid-column: 1 / -1;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: var(--pos-border-radius);
}

.table-card {
    position: relative; /* Needed for the pseudo-element */
    background: transparent; /* Main element is transparent */
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border-radius: var(--pos-border-radius);
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 1.5rem;
    overflow: hidden; /* Important to keep pseudo-element contained */
    z-index: 1; /* Establish a stacking context */
}

/* The new pseudo-element for the glass effect */
.table-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: -1; /* Sit behind the content */
    border-radius: inherit; /* Match parent's border radius */
    transition: background 0.25s ease;
}

.table-card:hover { 
    transform: translateY(-6px); 
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
}

/* The colored top border for status, using ::after */
.table-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background-color: transparent;
    transition: background-color 0.25s ease;
}
.table-card:hover::after {
    background-color: var(--pos-accent-primary);
}

.table-card-header {
    font-weight: 700;
    font-size: 2.5rem;
    color: var(--pos-text-primary);
    text-shadow: 0 1px 2px rgba(255,255,255,0.5);
    display: flex;
    align-items: center;
    gap: 10px;
}
.table-card-header .table-number-prefix {
    font-weight: 500;
    font-size: 1rem;
    color: var(--pos-text-secondary);
}

.table-card-footer {
    margin-top: 1rem;
}

.table-card .table-status { 
    font-size: 0.8rem; 
    font-weight: 600; 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
}

.table-card .party-list {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.table-card .party-tag {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--pos-text-secondary);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

/* Table Status Styles */
.table-card.available:hover::before {
    background: rgba(255, 255, 255, 0.7);
}
.table-card.available:hover {
     border-color: var(--pos-accent-success);
}
.table-card.available .table-status {
    color: var(--pos-status-available-text);
}

.table-card.partially-occupied::after { background-color: #fd7e14; }
.table-card.partially-occupied .table-status { color: #b45309; }
.table-card.partially-occupied:hover::after { background-color: #e85d04; }


.table-card.full::after { background-color: var(--pos-accent-danger); }
.table-card.full .table-status { color: #b91c1c; }
.table-card.full:hover::after { background-color: #a4161a; }


/* --- VIEW 2: ORDER VIEW --- */
#order-view {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 1.5rem;
    height: 100vh;
    padding: 1.5rem;
    box-sizing: border-box;
}
#pos-ticket, #pos-menu {
    background-color: var(--pos-bg-secondary);
    border-radius: var(--pos-border-radius);
    box-shadow: var(--pos-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Ticket Panel in Order View */
#ticket-header { padding: 20px; border-bottom: 1px solid var(--pos-border-color); }
#ticket-header #btn-back-to-tables { margin-bottom: 20px; width: 100%; }
#ticket-header .active-order-header h3 { font-size: 1.75rem; margin: 0 0 4px 0; }
#ticket-header .active-order-header p { font-size: 1rem; color: var(--pos-text-secondary); margin: 0; }
#ticket-items-container { flex-grow: 1; overflow-y: auto; padding: 10px; }
.empty-ticket-message { text-align: center; padding: 50px 20px; color: var(--pos-text-muted); }
.ticket-item { display: grid; grid-template-columns: 1fr auto auto; gap: 10px; align-items: center; padding: 16px 10px; border-bottom: 1px solid var(--pos-bg-tertiary); }
.ticket-item .item-info .item-name { font-weight: 600; margin: 0 0 4px 0; }
.ticket-item .item-info .item-price { font-size: 0.85rem; color: var(--pos-text-muted); margin: 0; }
.ticket-item .item-controls { display: flex; align-items: center; gap: 8px; }
.ticket-item .btn-qty { width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--pos-border-color); background-color: var(--pos-bg-secondary); color: var(--pos-text-secondary); font-size: 1.2rem; line-height: 1; font-weight: 400; cursor: pointer; transition: all 0.2s ease; }
.ticket-item .btn-qty:hover { background-color: var(--pos-accent-primary); color: white; border-color: var(--pos-accent-primary); }
.ticket-item .item-qty { font-weight: 600; min-width: 24px; text-align: center; }
.ticket-item .item-subtotal { font-weight: 600; min-width: 70px; text-align: right; }
#ticket-footer { padding: 20px; border-top: 1px solid var(--pos-border-color); background-color: var(--pos-bg-primary); margin-top: auto; }
#ticket-footer .total-line { display: flex; justify-content: space-between; font-size: 1.75rem; font-weight: 700; }
#ticket-footer .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }

/* Menu Panel in Order View */
#menu-header { padding: 15px; border-bottom: 1px solid var(--pos-border-color); flex-shrink: 0; }
#menu-categories { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 5px; margin-top: 15px; }
.category-tab { padding: 8px 18px; border: 1px solid var(--pos-border-color); border-radius: 20px; background-color: var(--pos-bg-secondary); color: var(--pos-text-secondary); font-weight: 500; cursor: pointer; white-space: nowrap; transition: all 0.2s ease; }
.category-tab:hover { background-color: var(--pos-bg-tertiary); border-color: var(--pos-accent-primary); }
.category-tab.active { background-color: var(--pos-accent-primary); color: white; border-color: var(--pos-accent-primary); }
#menu-header .search-bar { position: relative; }
#menu-header .search-bar i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--pos-text-muted); }
#menu-search-input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid var(--pos-border-color); border-radius: var(--pos-border-radius-sm); font-size: 1rem; box-sizing: border-box; }
#menu-grid { flex-grow: 1; overflow-y: auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; align-content: flex-start; }
.empty-grid-message { text-align: center; padding: 40px 20px; color: var(--pos-text-muted); grid-column: 1 / -1; }
.menu-card { border: 1px solid transparent; border-radius: var(--pos-border-radius); background-color: var(--pos-bg-tertiary); cursor: pointer; transition: all 0.2s ease; overflow: hidden; display: flex; flex-direction: column; min-height: 180px; }
.menu-card:hover { transform: translateY(-5px); box-shadow: var(--pos-shadow); border-color: var(--pos-accent-primary); }
.menu-card-img { height: 110px; flex-shrink: 0; background-color: #e9ecef; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: var(--pos-text-muted); }
.menu-card-body { padding: 12px; text-align: center; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }
.menu-card-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 5px; }
.menu-card-price { color: var(--pos-accent-primary); font-weight: 600; }

/* --- MODALS & TOAST --- */
.pos-modal { position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; animation: modal-fade-in 0.3s ease forwards; }
@keyframes modal-fade-in { from { opacity: 0; } to { opacity: 1; } }
.pos-modal .modal-content { background-color: var(--pos-bg-secondary); border-radius: var(--pos-border-radius); box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 90%; max-width: 500px; overflow: hidden; animation: modal-scale-in 0.3s ease; }
@keyframes modal-scale-in { from { transform: scale(0.95); } to { transform: scale(1); } }
.pos-modal .modal-header { padding: 15px 20px; border-bottom: 1px solid var(--pos-border-color); display: flex; justify-content: space-between; align-items: center; }
.pos-modal .modal-header h2 { margin: 0; font-size: 1.2rem; }
.pos-modal .btn-close-modal { background: none; border: none; font-size: 1.8rem; line-height: 1; cursor: pointer; color: var(--pos-text-muted); padding: 0; }
.pos-modal .modal-body { padding: 25px; }
.pos-modal .modal-body p { margin-top: 0; color: var(--pos-text-secondary); }
.pos-modal .modal-footer { padding: 15px 20px; border-top: 1px solid var(--pos-border-color); background-color: var(--pos-bg-primary); display: flex; justify-content: flex-end; gap: 10px; }

/* Staff & Party Modals */
#modal-staff-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 15px; margin-top: 20px; }
.staff-card { padding: 15px; border: 1px solid var(--pos-border-color); border-radius: var(--pos-border-radius-sm); text-align: center; cursor: pointer; transition: all 0.2s ease; }
.staff-card:hover { border-color: var(--pos-accent-primary); background-color: var(--pos-bg-tertiary); transform: translateY(-2px); }
.staff-card i { font-size: 2.5rem; margin-bottom: 10px; color: var(--pos-text-muted); }
.staff-card span { font-weight: 500; }

#modal-party-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

/* Toast */
#pos-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); padding: 12px 25px; border-radius: var(--pos-border-radius-sm); color: white; font-weight: 500; z-index: 2000; opacity: 0; transform: translate(-50%, 20px); transition: all 0.4s cubic-bezier(0.21, 1.02, 0.73, 1); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
#pos-toast.show { opacity: 1; transform: translate(-50%, 0); }
#pos-toast.success { background-color: #212529; }
#pos-toast.error { background-color: var(--pos-accent-danger); }

/* Animation for spinning refresh icon */
#tables-view-header .fa-spin { animation: fa-spin 1.5s infinite linear; }
@keyframes fa-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

</style>
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
        <main id="tables-grid-container">
            <!-- Table cards will be dynamically inserted here -->
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
                    <p>Table: <span id="active-table-number"></span> | Party: <span id="active-party-name"></span></p>
                </div>
            </header>
            <div id="ticket-items-container">
                <!-- Items will be dynamically inserted here -->
            </div>
            <footer id="ticket-footer">
                <div class="totals">
                    <div class="total-line grand-total">
                        <span>Total</span>
                        <span id="ticket-total">Rs 0.00</span>
                    </div>
                </div>
                <div class="actions">
                    <button id="btn-cancel-order" class="btn-pos btn-danger">
                        <i class="fas fa-times-circle"></i> Cancel Order
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

<!-- MODALS -->
<div id="modal-select-party" class="pos-modal">
    <div class="modal-content">
        <header class="modal-header">
            <h2>Manage Parties for Table <span id="modal-party-table-number"></span></h2>
            <button class="btn-close-modal" aria-label="Close modal">&times;</button>
        </header>
        <div class="modal-body">
            <p>Select an existing party or create a new one.</p>
            <div id="modal-party-list"></div>
        </div>
        <footer class="modal-footer">
            <button id="btn-create-new-party" class="btn-pos btn-primary">
                <i class="fas fa-plus"></i> Create New Party
            </button>
        </footer>
    </div>
</div>

<div id="modal-assign-staff" class="pos-modal">
    <div class="modal-content">
        <header class="modal-header">
            <h2>Assign Staff</h2>
            <button class="btn-close-modal" aria-label="Close modal">&times;</button>
        </header>
        <div class="modal-body">
            <p>Assign a staff member to start a new party for <strong>Table <span id="modal-staff-table-number"></span></strong></p>
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
        allMenuItems: [], allCategories: [], tablesData: [], allStaff: [],
        currentOrderId: null, selectedTableId: null, selectedTableNumber: null,
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
            partyNameSpan: document.getElementById('active-party-name'),
            itemsContainer: document.getElementById('ticket-items-container'),
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
            grid: document.getElementById('tables-grid-container'),
            refreshBtn: document.getElementById('btn-refresh-tables'),
        },
        modals: {
            selectParty: document.getElementById('modal-select-party'),
            partyTableNum: document.getElementById('modal-party-table-number'),
            partyList: document.getElementById('modal-party-list'),
            createNewPartyBtn: document.getElementById('btn-create-new-party'),
            
            assignStaff: document.getElementById('modal-assign-staff'),
            staffTableNum: document.getElementById('modal-staff-table-number'),
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
            state.tablesData = data.tables_data;
            state.allStaff = data.staff;
            
            renderAll();
            
            const wasRestored = await checkSessionAndRestore();
            if (!wasRestored) {
                showToast('Ready!', false);
            }
        } catch (error) { /* Handled by apiCall */ }
    }

    async function checkSessionAndRestore() {
        try {
            const response = await apiCall('getPosSession');
            const orderId = response.order_id;
            if (orderId) {
                showToast('Restoring previous session...', false, true);
                const orderDetails = await apiCall('getOrderDetails', { body: { order_id: orderId } });
                if (!orderDetails.data) {
                    await apiCall('clearPosSession');
                    return false;
                }
                renderOrderTicket(orderDetails.data);
                showOrderView();
                showToast('Session restored.', false);
                return true;
            }
        } catch (error) {
            console.error("Failed to restore session, starting fresh.", error);
            await apiCall('clearPosSession').catch(e => console.error("Failed to clear bad session", e));
        }
        return false;
    }
    
    async function refreshTables() {
        const refreshBtnIcon = ui.tables.refreshBtn.querySelector('i');
        refreshBtnIcon.classList.add('fa-spin');
        try {
            const response = await apiCall('getInitialData');
            state.tablesData = response.data.tables_data;
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
        ui.views.order.style.display = 'grid';
    }

    function showTablesView() {
        ui.views.order.style.display = 'none';
        ui.views.tables.style.display = 'flex';
        state.currentOrderId = null;
        state.selectedTableId = null;
        state.selectedTableNumber = null;
    }

    // --- RENDER FUNCTIONS ---
    function renderAll() {
        renderTables();
        renderCategories();
        renderMenu();
        renderStaffModal();
    }

    function renderTables() {
        const container = ui.tables.grid;
        container.innerHTML = '';

        if (!state.tablesData || state.tablesData.length === 0) {
            container.innerHTML = '<p class="empty-section-message">No tables found. Try refreshing.</p>';
            return;
        }

        const occupiedStatuses = ['Partially Occupied', 'Full'];
        const occupiedTables = state.tablesData.filter(t => occupiedStatuses.includes(t.Status));
        const availableTables = state.tablesData.filter(t => t.Status === 'Available');

        // Helper to generate table cards
        const createTableCard = (table) => {
            const tableEl = document.createElement('div');
            const statusClass = table.Status.toLowerCase().replace(/ /g, '-');
            tableEl.className = `table-card ${statusClass}`;
            tableEl.dataset.tableId = table.TableID;
            tableEl.dataset.tableNumber = table.TableNumber;

            let partyTagsHTML = '';
            if (table.parties && table.parties.length > 0) {
                const tags = table.parties.map(p => `<span class="party-tag">${p.PartyIdentifier}</span>`).join('');
                partyTagsHTML = `<div class="party-list">${tags}</div>`;
            }

            tableEl.innerHTML = `
                <div class="table-card-header">
                    <span class="table-number-prefix">Table</span>
                    <span>${table.TableNumber}</span>
                </div>
                <div class="table-card-footer">
                    <span class="table-status">${table.Status}</span>
                    ${partyTagsHTML}
                </div>
            `;
            tableEl.addEventListener('click', () => handleTableClick(table.TableID, table.TableNumber));
            return tableEl;
        };

        // Create and append Reserved/Occupied section
        const occupiedSection = document.createElement('div');
        occupiedSection.className = 'tables-section';
        occupiedSection.innerHTML = `<h2 class="tables-section-header"><i class="fas fa-bookmark"></i> Reserved / Occupied</h2>`;
        const occupiedGrid = document.createElement('div');
        occupiedGrid.className = 'tables-grid';
        if (occupiedTables.length > 0) {
            occupiedTables.sort((a, b) => a.TableNumber - b.TableNumber).forEach(table => occupiedGrid.appendChild(createTableCard(table)));
        } else {
            occupiedGrid.innerHTML = '<p class="empty-section-message">All tables are available.</p>';
        }
        occupiedSection.appendChild(occupiedGrid);
        container.appendChild(occupiedSection);

        // Create and append Available section
        const availableSection = document.createElement('div');
        availableSection.className = 'tables-section';
        availableSection.innerHTML = `<h2 class="tables-section-header"><i class="fas fa-door-open"></i> Available</h2>`;
        const availableGrid = document.createElement('div');
        availableGrid.className = 'tables-grid';
        if (availableTables.length > 0) {
            availableTables.sort((a, b) => a.TableNumber - b.TableNumber).forEach(table => availableGrid.appendChild(createTableCard(table)));
        } else {
            availableGrid.innerHTML = '<p class="empty-section-message">No tables available at the moment.</p>';
        }
        availableSection.appendChild(availableGrid);
        container.appendChild(availableSection);
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
        ui.ticket.tableNumberSpan.textContent = orderInfo.TableNumber;
        ui.ticket.partyNameSpan.textContent = orderInfo.PartyIdentifier;

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
        ui.ticket.total.textContent = `Rs ${total.toFixed(2)}`;
    }

    function renderPartySelectionModal(parties) {
        const list = ui.modals.partyList;
        list.innerHTML = '';
        if (parties.length > 0) {
            parties.forEach(party => {
                const partyEl = document.createElement('button');
                partyEl.className = 'btn-pos btn-secondary btn-full-width';
                partyEl.innerHTML = `<i class="fas fa-users"></i> ${party.PartyIdentifier}`;
                partyEl.addEventListener('click', () => handlePartySelection(party.OrderID));
                list.appendChild(partyEl);
            });
        } else {
            list.innerHTML = '<p class="empty-section-message">No active parties. Create one below.</p>';
        }
    }

    // --- EVENT HANDLERS ---
    function setupEventListeners() {
        ui.tables.refreshBtn.addEventListener('click', refreshTables);
        
        ui.ticket.backBtn.addEventListener('click', async () => {
            try {
                await apiCall('clearPosSession');
                showTablesView();
                await refreshTables();
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

        ui.modals.createNewPartyBtn.addEventListener('click', () => {
            closeModal(ui.modals.selectParty);
            ui.modals.staffTableNum.textContent = state.selectedTableNumber;
            openModal(ui.modals.assignStaff);
        });
    }

    async function handleTableClick(tableId, tableNumber) {
        state.selectedTableId = tableId;
        state.selectedTableNumber = tableNumber;
        
        try {
            const response = await apiCall('getPartiesForTable', { body: { table_id: tableId } });
            renderPartySelectionModal(response.data);
            ui.modals.partyTableNum.textContent = tableNumber;
            openModal(ui.modals.selectParty);
        } catch (error) { /* Handled */ }
    }

    async function handlePartySelection(orderId) {
        closeModal(ui.modals.selectParty);
        if (!orderId) {
            showToast('This party does not have an active order yet.', true);
            return;
        }
        try {
            await apiCall('setPosSession', { 
                method: 'POST', 
                body: new URLSearchParams({ order_id: orderId }) 
            });
            const response = await apiCall('getOrderDetails', { body: { order_id: orderId } });
            renderOrderTicket(response.data);
            showOrderView();
        } catch (error) { /* Handled */ }
    }

    async function handleStaffSelection(staffId) {
        closeModal(ui.modals.assignStaff);
        try {
            const params = new URLSearchParams({ table_id: state.selectedTableId, staff_id: staffId });
            const response = await apiCall('createPartyAndOrder', { method: 'POST', body: params });
            
            showToast(response.message);
            await refreshTables();
            
            const newOrderId = response.order_id;
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
            const params = new URLSearchParams({ order_id: state.currentOrderId, menu_item_id: menuItemId });
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
        const text = isCompleting ? 'This will finalize the order and mark the party for billing. Continue?' : 'Are you sure you want to cancel this order? This action cannot be undone.';
        const confirmClass = isCompleting ? 'btn-success' : 'btn-danger';

        showConfirmationModal(title, text, confirmClass, async () => {
            try {
                const params = new URLSearchParams({ order_id: state.currentOrderId, status });
                const response = await apiCall('updateOrderStatus', { method: 'POST', body: params });

                await apiCall('clearPosSession');

                if (isCompleting) {
                    showToast('Order finalized. Redirecting to payment...');
                    window.location.href = `payments.php?order_id=${state.currentOrderId}`;
                } else {
                    showToast(response.message);
                    showTablesView();
                    await refreshTables();
                }
            } catch (error) {
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
