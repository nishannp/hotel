<?php 
// store_items.php
require_once 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Sales Management</title>
    <!-- Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body{
            margin-top:  -130px;
        }
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }

        /* Style for the active tab/filter */
        .tab-active {
            border-color: #3b82f6;
            color: #3b82f6;
            background-color: #eff6ff;
        }
        .filter-btn-active {
            background-color: #3b82f6;
            color: white;
        }

        /* Fix for single item stretching: align content to the start */
        #itemsGrid {
            align-content: flex-start;
        }

        /* Simple animation for items appearing */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div id="app" class="flex flex-col lg:flex-row h-screen">

        <!-- Left Side: Point of Sale (POS) -->
        <main class="w-full lg:w-3/5 p-4 flex flex-col">
            <header class="mb-4">
                <h1 class="text-3xl font-bold text-gray-800">Point of Sale</h1>
                <div class="relative mt-2">
                    <input type="text" id="searchInput" placeholder="Search for items..." class="w-full p-3 pl-10 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <i class="fa fa-search absolute top-3.5 left-4 text-gray-400"></i>
                </div>
            </header>
            <!-- Category Filters -->
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-gray-600 mb-2">Categories</h2>
                <div id="categoryFilters" class="flex flex-wrap gap-2">
                    <!-- Category buttons will be injected here -->
                </div>
            </div>
            <!-- Items Grid -->
            <div id="itemsGrid" class="flex-grow overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-2 bg-white rounded-lg shadow-inner">
                <!-- Item cards will be injected here by JavaScript -->
            </div>
        </main>

        <!-- Right Side: Cart and Sales History -->
        <aside class="w-full lg:w-2/5 bg-white border-l border-gray-200 flex flex-col">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button id="cartTab" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm focus:outline-none tab-active">
                        <i class="fas fa-shopping-cart mr-2"></i> Cart <span id="cartCountBadge" class="bg-blue-500 text-white text-xs font-bold rounded-full px-2 py-1 ml-1">0</span>
                    </button>
                    <button id="historyTab" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                        <i class="fas fa-history mr-2"></i> Sales History
                    </button>
                </nav>
            </div>

            <!-- Cart View -->
            <div id="cartView" class="flex flex-col flex-grow p-4">
                <div id="cartItemsContainer" class="flex-grow overflow-y-auto">
                    <!-- Cart items will be injected here -->
                    <p id="emptyCartMessage" class="text-center text-gray-500 mt-8">Your cart is empty.</p>
                </div>
                <!-- Cart Footer -->
                <div class="border-t pt-4 mt-4">
                    <div class="flex justify-between items-center font-bold text-lg mb-2">
                        <label for="grandTotalInput" class="text-gray-800">Grand Total:</label>
                        <div class="flex items-center">
                            <span class="mr-1 text-gray-800">Rs.</span>
                            <input type="number" id="grandTotalInput" class="text-right font-bold text-lg border-2 border-gray-200 bg-gray-50 rounded-md p-1 focus:outline-none focus:border-blue-500 focus:bg-white transition-all w-40" step="0.01">
                        </div>
                    </div>
                    <button id="recordSaleBtn" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg mt-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Record Sale
                    </button>
                </div>
            </div>

            <!-- Sales History View -->
            <div id="historyView" class="hidden flex-col flex-grow p-4">
                 <div class="relative mb-2">
                    <input type="text" id="historySearchInput" placeholder="Search by ID or items..." class="w-full p-2 pl-8 border rounded-lg">
                    <i class="fa fa-search absolute top-2.5 left-3 text-gray-400"></i>
                </div>
                <div class="flex-grow overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="salesHistoryBody" class="bg-white divide-y divide-gray-200">
                            <!-- Sales history rows will be injected here -->
                        </tbody>
                    </table>
                     <p id="noSalesMessage" class="text-center text-gray-500 mt-8 hidden">No sales history found.</p>
                </div>
            </div>
        </aside>

    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-5 right-5 bg-green-500 text-white py-2 px-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300">
        <p id="toastMessage"></p>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div id="modalIcon" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                     <i class="fas fa-trash text-red-600 fa-lg"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2" id="modalTitle">Confirm Action</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage">Are you sure?</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmBtn" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Confirm
                    </button>
                    <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-auto ml-2 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script type="module">
        // --- CONFIGURATION ---
        const API_URL = 'ajax/ajax_handler_store_sales.php';
        
        // Custom currency formatter for "Rs."
        const formatCurrency = (value) => `Rs. ${parseFloat(value).toFixed(2)}`;

        // --- STATE MANAGEMENT ---
        let storeItems = [];
        let cart = []; // { id, name, price, quantity, imageUrl }
        let salesHistory = [];
        let activeCategory = 'all';

        // --- DOM ELEMENTS ---
        const DOMElements = {
            itemsGrid: document.getElementById('itemsGrid'),
            searchInput: document.getElementById('searchInput'),
            categoryFilters: document.getElementById('categoryFilters'),
            cartItemsContainer: document.getElementById('cartItemsContainer'),
            grandTotalInput: document.getElementById('grandTotalInput'),
            cartCountBadge: document.getElementById('cartCountBadge'),
            recordSaleBtn: document.getElementById('recordSaleBtn'),
            emptyCartMessage: document.getElementById('emptyCartMessage'),
            salesHistoryBody: document.getElementById('salesHistoryBody'),
            historySearchInput: document.getElementById('historySearchInput'),
            noSalesMessage: document.getElementById('noSalesMessage'),
            cartTab: document.getElementById('cartTab'),
            historyTab: document.getElementById('historyTab'),
            cartView: document.getElementById('cartView'),
            historyView: document.getElementById('historyView'),
            toast: document.getElementById('toast'),
            toastMessage: document.getElementById('toastMessage'),
            confirmationModal: document.getElementById('confirmationModal'),
            modalTitle: document.getElementById('modalTitle'),
            modalMessage: document.getElementById('modalMessage'),
            modalIcon: document.getElementById('modalIcon'),
            confirmBtn: document.getElementById('confirmBtn'),
            cancelBtn: document.getElementById('cancelBtn')
        };

        // --- API CALLS ---
        async function apiCall(action, body = null) {
            const formData = new FormData();
            formData.append('action', action);
            if (body) {
                for (const key in body) {
                    formData.append(key, body[key]);
                }
            }

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const result = await response.json();
                if (!result.success) throw new Error(result.message || 'API call failed');
                return result.data;
            } catch (error) {
                console.error(`Error in action '${action}':`, error);
                showToast(`Error: ${error.message}`, 'error');
                return null;
            }
        }
        
        // --- RENDERING & FILTERING ---
        function applyFiltersAndRender() {
            const searchTerm = DOMElements.searchInput.value.toLowerCase();
            
            let filteredItems = storeItems;

            // 1. Filter by category
            if (activeCategory !== 'all') {
                filteredItems = filteredItems.filter(item => item.CategoryID == activeCategory);
            }

            // 2. Filter by search term
            if (searchTerm) {
                filteredItems = filteredItems.filter(item => item.Name.toLowerCase().includes(searchTerm));
            }
            
            renderStoreItems(filteredItems);
        }

        function renderStoreItems(itemsToRender) {
            DOMElements.itemsGrid.innerHTML = '';
            if (itemsToRender.length === 0) {
                DOMElements.itemsGrid.innerHTML = `<p class="col-span-full text-center text-gray-500 mt-8">No items found.</p>`;
                return;
            }
            itemsToRender.forEach(item => {
                const itemCard = document.createElement('div');
                itemCard.className = 'fade-in bg-white border rounded-lg p-3 flex flex-col cursor-pointer hover:shadow-lg transition-shadow duration-200';
                itemCard.dataset.itemId = item.StoreItemID;
                itemCard.innerHTML = `
                    <img src="${item.ImageUrl}" alt="${item.Name}" class="w-full h-32 object-cover rounded-md mb-3" onerror="this.onerror=null;this.src='https://placehold.co/300x300/e5e7eb/6b7280?text=No+Image';">
                    <div class="flex-grow">
                        <h3 class="font-semibold text-sm text-gray-800">${item.Name}</h3>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <p class="font-bold text-blue-600">${formatCurrency(item.Price)}</p>
                        <button class="add-to-cart-btn bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                `;
                itemCard.addEventListener('click', () => addToCart(item.StoreItemID));
                DOMElements.itemsGrid.appendChild(itemCard);
            });
        }
        
        function renderCategoryFilters() {
            DOMElements.categoryFilters.innerHTML = '';
            const categories = [...new Map(storeItems.map(item => [item.CategoryID, {id: item.CategoryID, name: item.CategoryName || `Category ${item.CategoryID}`}])).values()];

            const createButton = (id, name) => {
                const button = document.createElement('button');
                button.className = 'px-3 py-1 text-sm font-medium rounded-full border transition-colors';
                button.dataset.categoryId = id;
                button.textContent = name;
                button.classList.toggle('filter-btn-active', activeCategory == id);
                button.addEventListener('click', () => {
                    activeCategory = id;
                    applyFiltersAndRender();
                    renderCategoryFilters(); // Re-render to update active state
                });
                return button;
            };

            DOMElements.categoryFilters.appendChild(createButton('all', 'All'));
            categories.forEach(cat => DOMElements.categoryFilters.appendChild(createButton(cat.id, cat.name)));
        }

        function renderCart() {
            DOMElements.cartItemsContainer.innerHTML = '';
            if (cart.length === 0) {
                DOMElements.emptyCartMessage.style.display = 'block';
                DOMElements.recordSaleBtn.disabled = true;
            } else {
                DOMElements.emptyCartMessage.style.display = 'none';
                DOMElements.recordSaleBtn.disabled = false;
                cart.forEach(item => {
                    const cartItemEl = document.createElement('div');
                    cartItemEl.className = 'flex items-center justify-between p-2 rounded-lg hover:bg-gray-50';
                    cartItemEl.innerHTML = `
                        <div class="flex items-center min-w-0">
                            <img src="${item.imageUrl}" class="w-12 h-12 object-cover rounded-md mr-3 flex-shrink-0">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm truncate">${item.name}</p>
                                <p class="text-xs text-gray-500">${formatCurrency(item.price)}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button class="quantity-btn" data-id="${item.id}" data-change="-1">-</button>
                            <span class="w-8 text-center font-medium">${item.quantity}</span>
                            <button class="quantity-btn" data-id="${item.id}" data-change="1">+</button>
                            <button class="remove-btn text-red-500 hover:text-red-700" data-id="${item.id}"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                    DOMElements.cartItemsContainer.appendChild(cartItemEl);
                });
            }
            updateCartSummary();
        }
        
        function renderSalesHistory(historyToRender = salesHistory) {
            DOMElements.salesHistoryBody.innerHTML = '';
            DOMElements.noSalesMessage.classList.toggle('hidden', historyToRender.length > 0);
            
            historyToRender.forEach(sale => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${sale.TransactionID}</div>
                        <div class="text-xs text-gray-500">${new Date(sale.SaleTime).toLocaleString()}</div>
                        <div class="text-sm font-bold text-green-600">${formatCurrency(sale.GrandTotal)}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">${sale.ItemsSummary}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                        <button class="delete-sale-btn text-red-500 hover:text-red-700" data-id="${sale.TransactionID}">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </td>
                `;
                DOMElements.salesHistoryBody.appendChild(row);
            });
        }

        // --- CART LOGIC ---
        function addToCart(itemId) {
            const itemInStore = storeItems.find(i => i.StoreItemID == itemId);
            if (!itemInStore) return;

            const itemInCart = cart.find(i => i.id == itemId);
            if (itemInCart) {
                itemInCart.quantity++;
            } else {
                cart.push({
                    id: itemInStore.StoreItemID,
                    name: itemInStore.Name,
                    price: itemInStore.Price,
                    quantity: 1,
                    imageUrl: itemInStore.ImageUrl
                });
            }
            renderCart();
            showToast(`${itemInStore.Name} added to cart.`);
        }

        function updateQuantity(itemId, change) {
            const itemInCart = cart.find(i => i.id == itemId);
            if (itemInCart) {
                itemInCart.quantity += change;
                if (itemInCart.quantity <= 0) {
                    cart = cart.filter(i => i.id != itemId);
                }
            }
            renderCart();
        }

        function removeFromCart(itemId) {
            cart = cart.filter(i => i.id != itemId);
            renderCart();
        }
        
        function updateCartSummary() {
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            DOMElements.grandTotalInput.value = total.toFixed(2);
            const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
            DOMElements.cartCountBadge.textContent = itemCount;
            DOMElements.cartCountBadge.style.display = itemCount > 0 ? 'inline-block' : 'none';
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        // --- BUSINESS LOGIC ---
        async function handleRecordSale() {
            if (cart.length === 0) return;
            
            const finalTotal = parseFloat(DOMElements.grandTotalInput.value);
            if (isNaN(finalTotal) || finalTotal < 0) {
                showToast('Invalid total amount.', 'error');
                return;
            }

            const cartData = cart.map(item => ({ id: item.id, quantity: item.quantity }));
            
            showConfirmation(
                'Confirm Sale',
                `Record this sale with a total of ${formatCurrency(finalTotal)}?`,
                async () => {
                    const result = await apiCall('recordSale', { 
                        cart: JSON.stringify(cartData),
                        final_total: finalTotal
                    });
                    if (result !== null) {
                        showToast('Sale recorded successfully!', 'success');
                        clearCart();
                        fetchAllSales(); // Refresh history
                    }
                },
                'confirm'
            );
        }

        async function handleDeleteSale(transactionId) {
            showConfirmation(
                'Delete Sale',
                `Are you sure you want to delete transaction ${transactionId}? This action cannot be undone.`,
                async () => {
                    const result = await apiCall('deleteSale', { transaction_id: transactionId });
                    if (result !== null) {
                        showToast('Sale deleted successfully.', 'success');
                        fetchAllSales(); // Refresh history
                    }
                },
                'delete'
            );
        }

        // --- DATA FETCHING ---
        async function fetchAllItems() {
            // IMPORTANT: Assumes your 'fetchStoreItems' action now joins with a categories table
            // and returns 'CategoryName' along with 'CategoryID' for each item.
            const data = await apiCall('fetchStoreItems');
            if (data) {
                storeItems = data;
                applyFiltersAndRender();
                renderCategoryFilters();
            }
        }

        async function fetchAllSales() {
            const data = await apiCall('fetchAllSales');
            if (data) {
                salesHistory = data;
                renderSalesHistory();
            }
        }

        // --- UI UTILITIES ---
        function showToast(message, type = 'success') {
            DOMElements.toastMessage.textContent = message;
            DOMElements.toast.className = `fixed top-5 right-5 text-white py-2 px-4 rounded-lg shadow-lg transform transition-transform duration-300 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}`;
            DOMElements.toast.classList.remove('translate-x-full');
            setTimeout(() => {
                DOMElements.toast.classList.add('translate-x-full');
            }, 3000);
        }
        
        function showConfirmation(title, message, onConfirm, type = 'confirm') {
            DOMElements.modalTitle.textContent = title;
            DOMElements.modalMessage.textContent = message;

            if(type === 'delete') {
                DOMElements.modalIcon.innerHTML = `<i class="fas fa-trash text-red-600 fa-lg"></i>`;
                DOMElements.modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100';
                DOMElements.confirmBtn.className = 'px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500';
                DOMElements.confirmBtn.textContent = 'Delete';
            } else {
                DOMElements.modalIcon.innerHTML = `<i class="fas fa-check-circle text-green-600 fa-lg"></i>`;
                DOMElements.modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100';
                DOMElements.confirmBtn.className = 'px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500';
                DOMElements.confirmBtn.textContent = 'Confirm';
            }

            DOMElements.confirmationModal.classList.remove('hidden');

            const newConfirmBtn = DOMElements.confirmBtn.cloneNode(true);
            DOMElements.confirmBtn.parentNode.replaceChild(newConfirmBtn, DOMElements.confirmBtn);
            DOMElements.confirmBtn = newConfirmBtn;
            
            DOMElements.confirmBtn.onclick = () => {
                onConfirm();
                DOMElements.confirmationModal.classList.add('hidden');
            };
        }

        // --- EVENT LISTENERS ---
        function setupEventListeners() {
            DOMElements.searchInput.addEventListener('input', applyFiltersAndRender);

            DOMElements.historySearchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const filteredHistory = salesHistory.filter(sale => 
                    sale.TransactionID.toLowerCase().includes(searchTerm) || 
                    sale.ItemsSummary.toLowerCase().includes(searchTerm)
                );
                renderSalesHistory(filteredHistory);
            });

            DOMElements.cartItemsContainer.addEventListener('click', (e) => {
                const target = e.target.closest('button');
                if (!target) return;
                const itemId = target.dataset.id;
                if (target.classList.contains('quantity-btn')) {
                    updateQuantity(itemId, parseInt(target.dataset.change, 10));
                } else if (target.classList.contains('remove-btn')) {
                    removeFromCart(itemId);
                }
            });
            
            DOMElements.salesHistoryBody.addEventListener('click', (e) => {
                const target = e.target.closest('.delete-sale-btn');
                if (target) handleDeleteSale(target.dataset.id);
            });

            DOMElements.cartTab.addEventListener('click', () => {
                DOMElements.cartView.style.display = 'flex';
                DOMElements.historyView.style.display = 'none';
                DOMElements.cartTab.classList.add('tab-active');
                DOMElements.historyTab.classList.remove('tab-active');
            });
            DOMElements.historyTab.addEventListener('click', () => {
                DOMElements.cartView.style.display = 'none';
                DOMElements.historyView.style.display = 'flex';
                DOMElements.historyTab.classList.add('tab-active');
                DOMElements.cartTab.classList.remove('tab-active');
            });

            DOMElements.recordSaleBtn.addEventListener('click', handleRecordSale);
            DOMElements.cancelBtn.addEventListener('click', () => DOMElements.confirmationModal.classList.add('hidden'));
        }

        // --- INITIALIZATION ---
        function initializeApp() {
            console.log("Initializing Store Sales App...");
            fetchAllItems();
            fetchAllSales();
            setupEventListeners();
            renderCart();
        }

        initializeApp();
    </script>
</body>
</html>
