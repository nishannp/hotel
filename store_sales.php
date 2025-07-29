<?php 
// Assuming 'includes/header.php' contains necessary setup, like session_start()
require_once 'includes/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Point of Sale</title>
    
    <!-- Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <style>
        /* Custom Styles for a Polished Look */
        body {
            margin-top: -130px;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* Light gray background */
        }
        /* Custom scrollbar for a cleaner look */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* Animation for modals */
        .modal-enter {
            animation: fadeInScale 0.2s ease-out forwards;
        }
        .modal-leave {
            animation: fadeOutScale 0.2s ease-in forwards;
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes fadeOutScale {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
        /* Simple animation for toast notifications */
        @keyframes slideInUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideOutDown {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(100%); opacity: 0; }
        }
        .toast-enter {
            animation: slideInUp 0.3s ease-out forwards;
        }
        .toast-leave {
            animation: slideOutDown 0.3s ease-in forwards;
        }
    </style>
</head>
<body class="text-slate-800">

    <div class="flex h-screen" id="posContainer">
        
        <!-- Main Content: Item Grid and Filters -->
        <main class="flex-1 flex flex-col p-4 md:p-6 lg:p-8 overflow-hidden">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-900">Store Items</h1>
                <p class="text-slate-500">Select items to add to the current sale.</p>
            </div>
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="relative flex-grow">
                    <span class="material-icons-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                    <input type="text" id="itemSearch" placeholder="Search products by name..." class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <div class="relative">
                    <span class="material-icons-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">filter_list</span>
                    <select id="categoryFilter" class="w-full sm:w-64 appearance-none pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition bg-white">
                        <option value="all">All Categories</option>
                        <!-- Categories will be loaded here via JavaScript -->
                    </select>
                </div>
            </div>
            <!-- Item Grid -->
            <div id="itemGrid" class="flex-1 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 overflow-y-auto custom-scrollbar pr-2 items-start">
                <!-- JS will populate this area -->
            </div>
            <!-- Empty State for Grid -->
            <div id="gridEmptyState" class="hidden flex-1 flex-col items-center justify-center text-slate-500">
                <span class="material-icons-outlined text-6xl text-slate-400">widgets</span>
                <h2 class="mt-4 text-xl font-semibold">No Items Found</h2>
                <p class="mt-1">Try adjusting your search or filter criteria.</p>
            </div>
        </main>

        <!-- Sidebar: Cart & Actions -->
        <aside class="w-full md:w-96 bg-white border-l border-slate-200 flex flex-col shadow-lg">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-900">Current Sale</h2>
                <button id="clearCartBtn" title="Clear Cart" class="p-2 rounded-full text-slate-500 hover:bg-red-100 hover:text-red-600 transition-colors">
                    <span class="material-icons-outlined">delete_sweep</span>
                </button>
            </div>
            <!-- Cart Items List -->
            <div id="cartItems" class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-3">
                <!-- JS will populate this area -->
            </div>
            <!-- Empty State for Cart -->
            <div id="cartEmptyState" class="flex-1 flex flex-col items-center justify-center text-slate-500 p-4">
                <span class="material-icons-outlined text-6xl text-slate-400">shopping_cart</span>
                <p class="mt-4 text-center">Click on an item to add it to the sale.</p>
            </div>
            <!-- Cart Summary & Actions -->
            <div class="p-6 bg-slate-50 border-t border-slate-200 mt-auto">
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Subtotal</span>
                        <span id="cartSubtotal" class="font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-900 font-bold text-lg">
                        <span>Total</span>
                        <span id="cartTotal">$0.00</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <button id="completeSaleBtn" class="w-full flex items-center justify-center gap-2 bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:bg-indigo-700 transition-all disabled:bg-slate-300 disabled:cursor-not-allowed">
                        <span class="material-icons-outlined">payment</span>
                        Process Payment
                    </button>
                    <button id="showSalesLogBtn" class="w-full flex items-center justify-center gap-2 bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-lg hover:bg-slate-300 transition-colors">
                        <span class="material-icons-outlined">history</span>
                        View Sales History
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Sales Log Modal -->
    <div id="salesLogModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col modal-enter">
            <div class="p-5 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-3"><span class="material-icons-outlined">history</span>Sales History</h2>
                <button id="closeLogModalBtn" class="p-2 rounded-full hover:bg-slate-100">&times;</button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <div id="logEmptyState" class="hidden flex-col items-center justify-center text-slate-500 py-16">
                    <span class="material-icons-outlined text-6xl text-slate-400">receipt_long</span>
                    <h4 class="mt-4 text-xl font-semibold">No Sales Recorded</h4>
                    <p>Completed sales will appear here.</p>
                </div>
                <div class="overflow-x-auto">
                    <table id="salesLogTable" class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 rounded-l-lg">Transaction ID</th>
                                <th scope="col" class="px-6 py-3">Items Summary</th>
                                <th scope="col" class="px-6 py-3">Total</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3 rounded-r-lg">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md modal-enter">
            <div class="p-5 border-b border-slate-200 flex justify-between items-center">
                <h2 id="confirmationTitle" class="text-lg font-bold text-slate-900">Confirm Action</h2>
                 <button id="closeConfirmationModalBtn" class="p-2 rounded-full hover:bg-slate-100">&times;</button>
            </div>
            <div class="p-6">
                <p id="confirmationMessage" class="text-slate-600">Are you sure?</p>
            </div>
            <div class="p-4 bg-slate-50 flex justify-end gap-3 rounded-b-xl">
                <button id="cancelBtn" class="px-4 py-2 bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 transition">Cancel</button>
                <button id="confirmBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Placeholder -->
    <div id="toastNotification" class="fixed bottom-5 right-5 w-full max-w-xs p-4 rounded-lg shadow-lg text-white z-50 hidden">
        <div class="flex items-center gap-3">
            <span id="toastIcon" class="material-icons-outlined"></span>
            <p id="toastMessage"></p>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main Application Object
    const POS_App = {
        // Cache DOM elements for performance
        elements: {
            itemGrid: document.getElementById('itemGrid'),
            gridEmptyState: document.getElementById('gridEmptyState'),
            itemSearch: document.getElementById('itemSearch'),
            categoryFilter: document.getElementById('categoryFilter'),
            cartItems: document.getElementById('cartItems'),
            cartEmptyState: document.getElementById('cartEmptyState'),
            cartSubtotal: document.getElementById('cartSubtotal'),
            cartTotal: document.getElementById('cartTotal'),
            completeSaleBtn: document.getElementById('completeSaleBtn'),
            clearCartBtn: document.getElementById('clearCartBtn'),
            salesLogModal: document.getElementById('salesLogModal'),
            showSalesLogBtn: document.getElementById('showSalesLogBtn'),
            closeLogModalBtn: document.getElementById('closeLogModalBtn'),
            salesLogTable: document.getElementById('salesLogTable'),
            salesLogTableBody: document.querySelector('#salesLogTable tbody'),
            logEmptyState: document.getElementById('logEmptyState'),
            confirmationModal: document.getElementById('confirmationModal'),
            confirmationTitle: document.getElementById('confirmationTitle'),
            confirmationMessage: document.getElementById('confirmationMessage'),
            confirmBtn: document.getElementById('confirmBtn'),
            cancelBtn: document.getElementById('cancelBtn'),
            closeConfirmationModalBtn: document.getElementById('closeConfirmationModalBtn'),
            toast: document.getElementById('toastNotification'),
            toastIcon: document.getElementById('toastIcon'),
            toastMessage: document.getElementById('toastMessage'),
        },
        // Application state
        state: {
            allItems: [],
            cart: [],
            currentFilter: { category: 'all', search: '' },
            confirmAction: null,
            toastTimeout: null,
        },

        // Initialize the application
        init() {
            this.loadInitialData();
            this.bindEvents();
            this.updateCartUI(); // Initial UI update
        },

        // Fetch initial data for items and categories
        async loadInitialData() {
            try {
                const [itemsRes, categoriesRes] = await Promise.all([
                    fetch('ajax/ajax_handler_store_sales.php?action=fetchStoreItems'),
                    fetch('ajax/ajax_handler_store_categories.php?action=fetchAll')
                ]);
                const itemsData = await itemsRes.json();
                const categoriesData = await categoriesRes.json();

                if (itemsData.success) {
                    this.state.allItems = itemsData.data;
                    this.renderItemGrid();
                } else this.showToast('Failed to load store items.', 'error');

                if (categoriesData.success) {
                    this.populateCategoryFilter(categoriesData.data);
                } else this.showToast('Failed to load categories.', 'error');

            } catch (error) {
                console.error("Data loading error:", error);
                this.showToast('Error connecting to the server.', 'error');
            }
        },

        // Bind all event listeners
        bindEvents() {
            // Item filtering
            this.elements.itemSearch.addEventListener('input', () => {
                this.state.currentFilter.search = this.elements.itemSearch.value.toLowerCase();
                this.renderItemGrid();
            });
            this.elements.categoryFilter.addEventListener('change', () => {
                this.state.currentFilter.category = this.elements.categoryFilter.value;
                this.renderItemGrid();
            });

            // Add item to cart
            this.elements.itemGrid.addEventListener('click', e => {
                const card = e.target.closest('.item-card');
                if (card) this.addItemToCart(card.dataset.id);
            });

            // Cart interactions (quantity change, remove)
            this.elements.cartItems.addEventListener('click', e => {
                const cartItem = e.target.closest('.cart-item');
                if (!cartItem) return;
                const itemId = cartItem.dataset.id;
                if (e.target.closest('.quantity-increase')) this.updateCartQuantity(itemId, 1);
                if (e.target.closest('.quantity-decrease')) this.updateCartQuantity(itemId, -1);
                if (e.target.closest('.remove-item-btn')) this.updateCartQuantity(itemId, -Infinity); // Special value for removal
            });

            // Cart actions
            this.elements.clearCartBtn.addEventListener('click', () => {
                if (this.state.cart.length > 0) {
                    this.showConfirmation('Clear Cart?', 'Are you sure you want to remove all items from the current sale?', () => {
                        this.clearCart();
                        this.hideConfirmation();
                    });
                }
            });
            this.elements.completeSaleBtn.addEventListener('click', () => this.confirmSale());

            // Modal controls
            this.elements.showSalesLogBtn.addEventListener('click', () => this.showSalesLog());
            this.elements.closeLogModalBtn.addEventListener('click', () => this.hideModal(this.elements.salesLogModal));
            this.elements.salesLogTableBody.addEventListener('click', e => {
                if (e.target.closest('.delete-btn')) {
                    this.confirmDelete(e.target.closest('.delete-btn').dataset.id);
                }
            });
            
            // Confirmation modal buttons
            this.elements.confirmBtn.addEventListener('click', () => {
                if (typeof this.state.confirmAction === 'function') {
                    this.state.confirmAction();
                }
            });
            [this.elements.cancelBtn, this.elements.closeConfirmationModalBtn].forEach(btn => {
                btn.addEventListener('click', () => this.hideConfirmation());
            });
        },

        // Render the grid of store items based on filters
        renderItemGrid() {
            const { category, search } = this.state.currentFilter;
            const filteredItems = this.state.allItems.filter(item => 
                (category === 'all' || item.CategoryID === category) &&
                (item.Name.toLowerCase().includes(search) || (item.Description && item.Description.toLowerCase().includes(search)))
            );
            
            this.elements.itemGrid.innerHTML = '';
            const showEmptyState = filteredItems.length === 0;
            this.elements.gridEmptyState.classList.toggle('hidden', !showEmptyState);
            this.elements.gridEmptyState.classList.toggle('flex', showEmptyState);
            this.elements.itemGrid.classList.toggle('hidden', showEmptyState);

            filteredItems.forEach(item => {
                const card = document.createElement('div');
                card.className = 'item-card group cursor-pointer bg-white rounded-lg shadow-sm hover:shadow-xl transition-shadow duration-300 flex flex-col overflow-hidden border border-slate-200';
                card.dataset.id = item.StoreItemID;
                const price = parseFloat(item.Price).toFixed(2);
                
                card.innerHTML = `
                    <div class="relative pt-[100%]"> <!-- Aspect ratio 1:1 -->
                        <img src="${this.escapeHTML(item.ImageUrl)}" alt="${this.escapeHTML(item.Name)}" class="absolute top-0 left-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.onerror=null;this.src='https://placehold.co/400x400/e2e8f0/94a3b8?text=Image+Not+Found';">
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <h3 class="font-semibold text-slate-800 truncate" title="${this.escapeHTML(item.Name)}">${this.escapeHTML(item.Name)}</h3>
                        <p class="text-indigo-600 font-bold mt-2">$${price}</p>
                    </div>
                `;
                this.elements.itemGrid.appendChild(card);
            });
        },

        // Populate the category filter dropdown
        populateCategoryFilter(categories) {
            categories.forEach(cat => {
                this.elements.categoryFilter.innerHTML += `<option value="${cat.CategoryID}">${this.escapeHTML(cat.CategoryName)}</option>`;
            });
        },

        // Add an item to the cart or increment its quantity
        addItemToCart(itemId) {
            const existingItem = this.state.cart.find(item => item.id === itemId);
            const itemData = this.state.allItems.find(item => item.StoreItemID === itemId);
            if (!itemData) return;

            if (existingItem) {
                existingItem.quantity++;
            } else {
                this.state.cart.push({
                    id: itemData.StoreItemID,
                    name: itemData.Name,
                    price: parseFloat(itemData.Price),
                    quantity: 1,
                });
            }
            this.showToast(`${this.escapeHTML(itemData.name)} added to cart.`, 'success');
            this.updateCartUI();
        },

        // Update item quantity in the cart
        updateCartQuantity(itemId, change) {
            const itemIndex = this.state.cart.findIndex(item => item.id === itemId);
            if (itemIndex === -1) return;

            if (change === -Infinity) { // Remove item
                this.state.cart.splice(itemIndex, 1);
            } else {
                this.state.cart[itemIndex].quantity += change;
                if (this.state.cart[itemIndex].quantity <= 0) {
                    this.state.cart.splice(itemIndex, 1); // Remove if quantity is 0 or less
                }
            }
            this.updateCartUI();
        },

        // Re-render the entire cart UI
        updateCartUI() {
            this.renderCartItems();
            this.updateCartSummary();
            const hasItems = this.state.cart.length > 0;
            this.elements.cartEmptyState.classList.toggle('hidden', hasItems);
            this.elements.cartEmptyState.classList.toggle('flex', !hasItems);
            this.elements.cartItems.classList.toggle('hidden', !hasItems);
            this.elements.completeSaleBtn.disabled = !hasItems;
        },

        // Render the list of items in the cart
        renderCartItems() {
            this.elements.cartItems.innerHTML = '';
            this.state.cart.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'cart-item flex items-center gap-4 p-2 bg-slate-50 rounded-lg';
                itemEl.dataset.id = item.id;
                const itemTotal = (item.price * item.quantity).toFixed(2);

                itemEl.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-slate-800 truncate">${this.escapeHTML(item.name)}</p>
                        <p class="text-xs text-slate-500">$${item.price.toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="quantity-decrease w-7 h-7 bg-slate-200 rounded-md hover:bg-slate-300 transition flex-shrink-0">-</button>
                        <span class="font-medium w-5 text-center">${item.quantity}</span>
                        <button class="quantity-increase w-7 h-7 bg-slate-200 rounded-md hover:bg-slate-300 transition flex-shrink-0">+</button>
                    </div>
                    <p class="font-semibold w-16 text-right flex-shrink-0">$${itemTotal}</p>
                    <button class="remove-item-btn text-slate-400 hover:text-red-500 transition flex-shrink-0" title="Remove Item">&times;</button>
                `;
                this.elements.cartItems.appendChild(itemEl);
            });
        },

        // Update the subtotal, tax, and total in the cart summary
        updateCartSummary() {
            const subtotal = this.state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            this.elements.cartSubtotal.textContent = `${subtotal.toFixed(2)}`;
            this.elements.cartTotal.textContent = `${subtotal.toFixed(2)}`;
        },

        // Clear all items from the cart
        clearCart() {
            this.state.cart = [];
            this.updateCartUI();
            this.showToast('Cart cleared.', 'info');
        },

        // Show confirmation before processing the sale
        confirmSale() {
            this.showConfirmation('Process Sale?', `The total amount is $${this.elements.cartTotal.textContent.substring(1)}. Proceed with payment?`, () => this.executeSale());
        },

        // Send sale data to the server
        async executeSale() {
            this.hideConfirmation();
            const cartData = this.state.cart.map(item => ({ id: item.id, quantity: item.quantity }));
            const formData = new FormData();
            formData.append('action', 'recordSale');
            formData.append('cart', JSON.stringify(cartData));

            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.showToast(data.message || 'Sale completed successfully!', 'success');
                    this.clearCart();
                } else { throw new Error(data.message || 'An unknown error occurred.'); }
            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        // Show confirmation before deleting a transaction
        confirmDelete(transactionId) {
            this.showConfirmation('Delete Transaction?', 'Are you sure you want to permanently delete this transaction? This action cannot be undone.', () => this.executeDelete(transactionId));
        },

        // Send delete request to the server
        async executeDelete(transactionId) {
            this.hideConfirmation();
            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=deleteSale&transaction_id=${transactionId}`
                });
                const data = await response.json();
                if (data.success) {
                    this.showToast(data.message || 'Transaction deleted.', 'success');
                    this.showSalesLog(); // Refresh the log
                } else { throw new Error(data.message || 'Failed to delete transaction.'); }
            } catch (error) {
                this.showToast(error.message, 'error');
            }
        },

        // Fetch and display the sales history log
        async showSalesLog() {
            this.showModal(this.elements.salesLogModal); // Show modal immediately for better UX
            try {
                const response = await fetch('ajax/ajax_handler_store_sales.php?action=fetchAllSales');
                const data = await response.json();
                const logBody = this.elements.salesLogTableBody;
                logBody.innerHTML = '';

                if (!data.success) {
                    throw new Error(data.message || 'An unknown error occurred.');
                }

                const hasSales = data.data.length > 0;
                this.elements.logEmptyState.classList.toggle('hidden', hasSales);
                this.elements.logEmptyState.classList.toggle('flex', !hasSales);
                this.elements.salesLogTable.parentElement.classList.toggle('hidden', !hasSales);

                if (hasSales) {
                    data.data.forEach(sale => {
                        const row = logBody.insertRow();
                        row.className = 'bg-white border-b hover:bg-slate-50';
                        row.innerHTML = `
                            <td class="px-6 py-4 font-medium text-slate-900">${this.escapeHTML(sale.TransactionID)}</td>
                            <td class="px-6 py-4">${this.escapeHTML(sale.ItemsSummary)}</td>
                            <td class="px-6 py-4 font-medium">${parseFloat(sale.GrandTotal).toFixed(2)}</td>
                            <td class="px-6 py-4">${new Date(sale.SaleTime).toLocaleString()}</td>
                            <td class="px-6 py-4">
                                <button class="delete-btn p-2 rounded-full text-slate-500 hover:bg-red-100 hover:text-red-600 transition" data-id="${this.escapeHTML(sale.TransactionID)}" title="Delete Transaction">
                                    <span class="material-icons-outlined text-base">delete</span>
                                </button>
                            </td>
                        `;
                    });
                }
            } catch (error) {
                console.error("Sales log loading error:", error);
                this.showToast(error.message || 'Failed to load sales history.', 'error');
                // Ensure the modal shows an empty/error state
                this.elements.salesLogTableBody.innerHTML = '';
                this.elements.logEmptyState.classList.remove('hidden');
                this.elements.logEmptyState.classList.add('flex');
                this.elements.salesLogTable.parentElement.classList.add('hidden');
            }
        },

        // Modal visibility controls
        showModal(modal) { 
            modal.classList.remove('hidden');
            modal.firstElementChild.classList.remove('modal-leave');
            modal.firstElementChild.classList.add('modal-enter');
        },
        hideModal(modal) { 
            modal.firstElementChild.classList.remove('modal-enter');
            modal.firstElementChild.classList.add('modal-leave');
            setTimeout(() => modal.classList.add('hidden'), 200);
        },
        
        // Confirmation modal controls
        showConfirmation(title, message, action) {
            this.elements.confirmationTitle.textContent = title;
            this.elements.confirmationMessage.textContent = message;
            this.state.confirmAction = action;
            this.showModal(this.elements.confirmationModal);
        },
        hideConfirmation() {
            this.hideModal(this.elements.confirmationModal);
            this.state.confirmAction = null;
        },

        // Toast notification handler
        showToast(message, type = 'success') {
            if (this.state.toastTimeout) clearTimeout(this.state.toastTimeout);
            
            const toast = this.elements.toast;
            this.elements.toastMessage.textContent = message;
            
            toast.className = 'fixed bottom-5 right-5 w-full max-w-xs p-4 rounded-lg shadow-lg text-white z-50'; // Reset classes
            toast.classList.add('toast-enter');

            if (type === 'error') {
                this.elements.toastIcon.textContent = 'error';
                toast.classList.add('bg-red-600');
            } else if (type === 'info') {
                 this.elements.toastIcon.textContent = 'info';
                toast.classList.add('bg-blue-600');
            } else {
                this.elements.toastIcon.textContent = 'check_circle';
                toast.classList.add('bg-green-600');
            }
            
            toast.classList.remove('hidden');

            this.state.toastTimeout = setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-leave');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        },

        // Utility to prevent XSS
        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            const p = document.createElement('p');
            p.textContent = str;
            return p.innerHTML;
        }
    };

    // Start the application
    POS_App.init();
});
</script>

</body>
</html>
<?php 
// Assuming 'includes/footer.php' might contain cleanup code or closing tags
require_once 'includes/footer.php'; 
?>
