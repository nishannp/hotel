<?php 
// menu_items.php
require_once 'includes/header.php'; 
// We'll set the header text with JS in a more robust way later
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<style>
/* 
   =================================
   ||  Creative Menu UI - Light Theme  ||
   =================================
*/

:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #4f46e5; /* A vibrant indigo */
    --primary-hover: #4338ca;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --font-family: 'Poppins', sans-serif;
    --drawer-width: 450px;
}

/* Base & Layout */
.content-wrapper {
    background-color: var(--bg-main);
    font-family: var(--font-family);
}
.content-header h1 {
    color: var(--text-primary) !important;
    font-weight: 600 !important;
}
.menu-container {
    padding: 2rem;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.page-header h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.2s ease, transform 0.1s ease;
    box-shadow: var(--shadow-sm);
}
.btn-primary:hover {
    background-color: var(--primary-hover);
    box-shadow: var(--shadow-md);
}
.btn-primary:active {
    transform: scale(0.98);
}
.btn-primary .material-icons-outlined { font-size: 20px; }

/* Filter Bar */
.menu-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem;
    background-color: var(--bg-content);
    border-radius: 10px;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}
.search-bar {
    display: flex;
    align-items: center;
    background-color: var(--bg-main);
    border-radius: 8px;
    padding: 0 8px;
    flex-grow: 1;
    min-width: 250px;
}
.search-bar .material-icons-outlined {
    color: var(--text-secondary);
}
.search-bar input {
    border: none;
    background: transparent;
    padding: 10px;
    width: 100%;
    font-size: 0.95rem;
}
.search-bar input:focus {
    outline: none;
}
.category-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.filter-btn {
    padding: 8px 16px;
    border: none;
    background-color: transparent;
    color: var(--text-secondary);
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.filter-btn:hover {
    background-color: #f3f4f6;
    color: var(--text-primary);
}
.filter-btn.active {
    background-color: var(--primary-color);
    color: white;
    box-shadow: var(--shadow-md);
}

/* Category Sections & Item Grid */
#menu-content {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}
.category-section {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeIn 0.5s ease-out forwards;
}
@keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }

.category-header {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
    display: inline-block;
}
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Item Card Design */
.menu-item-card {
    background-color: var(--bg-content);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.menu-item-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.card-image-wrapper {
    width: 100%;
    height: 190px;
    position: relative;
    background-color: #f3f4f6; /* Placeholder background */
}
.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.4s ease, transform 0.4s ease;
    opacity: 0; /* Hidden until loaded */
}
.card-img.loaded {
    opacity: 1;
}
.menu-item-card:hover .card-img.loaded {
    transform: scale(1.05);
}
.card-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 8px;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.2s ease-in-out;
}
.menu-item-card:hover .card-actions {
    opacity: 1;
    transform: translateY(0);
}
.card-actions button {
    width: 36px; height: 36px;
    border-radius: 50%; border: none;
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    color: var(--text-primary);
    cursor: pointer;
    display: grid; place-items: center;
    box-shadow: var(--shadow-md);
    transition: transform 0.2s, background-color 0.2s;
}
.card-actions button:hover {
    transform: scale(1.1);
    background-color: white;
}
.card-actions .material-icons-outlined { font-size: 20px; }

.card-content {
    padding: 1rem 1.25rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.card-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}
.card-category {
    color: var(--text-secondary);
    font-size: 0.85rem;
    margin-bottom: 1rem;
}
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto; /* Pushes footer to the bottom */
    padding-top: 1rem;
}
.card-price {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--primary-color);
}
.availability-badge {
    font-size: 0.8rem;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 99px;
    color: white;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.availability-badge.available { background-color: var(--success-color); }
.availability-badge.not-available { background-color: var(--danger-color); }

/* Empty State */
.empty-state {
    display: none; /* Hidden by default */
    text-align: center;
    padding: 4rem 2rem;
    background-color: var(--bg-content);
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    color: var(--text-secondary);
}
.empty-state .material-icons-outlined {
    font-size: 64px;
    color: var(--primary-color);
    opacity: 0.5;
}
.empty-state h3 {
    margin-top: 1rem;
    font-size: 1.5rem;
    color: var(--text-primary);
}

/* Off-Canvas Form Drawer */
.form-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: var(--drawer-width);
    height: 100%;
    background-color: var(--bg-content);
    box-shadow: -10px 0 30px rgba(0,0,0,0.1);
    transform: translateX(100%);
    transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    z-index: 1050; /* Above AdminLTE overlay */
    display: flex;
    flex-direction: column;
}
.form-drawer.is-open {
    transform: translateX(0);
}
.drawer-header {
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
}
#drawerTitle {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}
.close-drawer-btn {
    background: #f3f4f6;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: grid;
    place-items: center;
    color: var(--text-secondary);
    transition: all 0.2s ease;
}
.close-drawer-btn:hover {
    background: #e5e7eb;
    color: var(--text-primary);
    transform: rotate(90deg);
}
.drawer-body {
    padding: 2rem;
    overflow-y: auto;
    flex-grow: 1;
}
.drawer-body .form-group {
    margin-bottom: 1.5rem;
}
.drawer-body label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.9rem;
}
.drawer-body input[type="text"],
.drawer-body input[type="number"],
.drawer-body select,
.drawer-body textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--bg-main);
    transition: all 0.2s ease;
}
.drawer-body input:focus, .drawer-body select:focus, .drawer-body textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}
.drawer-body textarea {
    resize: vertical;
    min-height: 100px;
}
/* Custom Checkbox */
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: var(--bg-main);
    padding: 12px;
    border-radius: 8px;
}

.drawer-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid var(--border-color);
    flex-shrink: 0;
    background-color: var(--bg-content);
}
.drawer-footer button {
    width: 100%;
    padding: 14px 22px;
}

/* Image Upload Area */
#imageUploadArea {
    width: 100%;
    height: 200px;
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color 0.2s;
    position: relative;
    overflow: hidden;
    background-color: #fcfcfd;
}
#imageUploadArea:hover {
    border-color: var(--primary-color);
}
#imagePreview {
    width: 100%; height: 100%;
    object-fit: cover;
    position: absolute;
    opacity: 0;
    transition: opacity 0.3s ease;
}
#imagePreview.has-image { opacity: 1; }
#imageUploadPlaceholder {
    color: var(--text-secondary); text-align: center;
}
#imageUploadPlaceholder .material-icons-outlined {
    font-size: 48px; color: #cbd5e1;
}
</style>

<div class="menu-container">
    <div class="page-header">
        <h2 id="main-title">Menu Items</h2>
        <button id="addItemBtn" class="btn-primary">
            <span class="material-icons-outlined">add_circle</span>
            Add New Item
        </button>
    </div>

    <div class="menu-filters">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search for items...">
        </div>
        <div id="categoryFilters" class="category-filters"></div>
    </div>

    <div id="menu-content">
        <!-- Categories and items will be dynamically inserted here -->
    </div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">search</span>
        <h3>No Items Found</h3>
        <p>Try adjusting your search or filter criteria.</p>
    </div>
</div>

<!-- Add/Edit Item Drawer -->
<aside id="itemDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Item</h3>
        <button class="close-drawer-btn" title="Close">
            <span class="material-icons-outlined">close</span>
        </button>
    </div>
    <form id="itemForm" class="drawer-body" enctype="multipart/form-data">
        <input type="hidden" id="menuItemId" name="menu_item_id">
        <input type="hidden" id="existingImagePath" name="existing_image_path">
        
        <div class="form-group">
            <div id="imageUploadArea" onclick="document.getElementById('itemImage').click();">
                <img id="imagePreview" src="#">
                <div id="imageUploadPlaceholder">
                    <span class="material-icons-outlined">cloud_upload</span>
                    <p style="font-weight: 500; margin-top: 0.5rem;">Click to upload image</p>
                    <p style="font-size: 0.8rem;">PNG, JPG, WEBP up to 5MB</p>
                </div>
            </div>
            <input type="file" id="itemImage" name="item_image" accept="image/*" style="display:none;">
        </div>

        <div class="form-group">
            <label for="itemName">Item Name</label>
            <input type="text" id="itemName" name="item_name" required placeholder="e.g., Classic Margherita Pizza">
        </div>
        <div class="form-group">
            <label for="itemCategory">Category</label>
            <select id="itemCategory" name="item_category" required></select>
        </div>
        <div class="form-group">
            <label for="itemPrice">Price ($)</label>
            <input type="number" id="itemPrice" name="item_price" step="0.01" required placeholder="e.g., 12.99">
        </div>
        <div class="form-group">
            <label for="itemDescription">Description</label>
            <textarea id="itemDescription" name="item_description" rows="4" placeholder="Briefly describe the item..."></textarea>
        </div>
        <div class="form-group">
            <label>Availability</label>
            <div class="checkbox-group">
                 <input type="checkbox" id="isAvailable" name="is_available" checked>
                 <label for="isAvailable" style="margin-bottom: 0; cursor: pointer;">This item is currently available</label>
            </div>
        </div>

        <hr style="margin: 2rem 0; border-color: var(--border-color);">

        <!-- Ingredients Section -->
        <div class="form-group">
            <h4 style="font-weight: 600; color: var(--text-primary); margin-bottom: 1rem;">Recipe Ingredients</h4>
            
            <div id="ingredient-selector" style="display: flex; gap: 10px; margin-bottom: 1rem;">
                <select id="ingredientList" style="flex-grow: 1;"></select>
                <input type="number" id="ingredientQty" placeholder="Qty" step="0.001" style="width: 100px;">
                <button type="button" id="addIngredientBtn" class="btn-primary" style="padding: 10px 15px; white-space: nowrap;">Add</button>
            </div>

            <div id="itemIngredientsList">
                <!-- Dynamically added ingredients will appear here -->
            </div>
        </div>

    </form>
    <div class="drawer-footer">
        <button type="submit" form="itemForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Item
        </button>
    </div>
</aside>

<style>
/* Additional styles for ingredients section */
#itemIngredientsList .ingredient-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: var(--bg-main);
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: 0.9rem;
}
#itemIngredientsList .ingredient-name {
    flex-grow: 1;
    font-weight: 500;
}
#itemIngredientsList .ingredient-qty {
    color: var(--text-secondary);
}
#itemIngredientsList .ingredient-unit {
    color: var(--text-secondary);
    font-style: italic;
    min-width: 30px;
}
#itemIngredientsList .remove-ingredient-btn {
    background: none;
    border: none;
    color: var(--danger-color);
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}
#itemIngredientsList .remove-ingredient-btn:hover {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set header title dynamically
    document.querySelector('.content-header h1').textContent = 'Menu Management';

    const MenuApp = {
        // DOM Elements
        elements: {
            drawer: document.getElementById('itemDrawer'),
            form: document.getElementById('itemForm'),
            menuContent: document.getElementById('menu-content'),
            categoryFilters: document.getElementById('categoryFilters'),
            emptyState: document.getElementById('emptyState'),
            addItemBtn: document.getElementById('addItemBtn'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            imageInput: document.getElementById('itemImage'),
            imagePreview: document.getElementById('imagePreview'),
            imageUploadPlaceholder: document.getElementById('imageUploadPlaceholder'),
            searchInput: document.getElementById('searchInput'),
            // Ingredient specific elements
            addIngredientBtn: document.getElementById('addIngredientBtn'),
            ingredientListSelect: document.getElementById('ingredientList'),
            ingredientQtyInput: document.getElementById('ingredientQty'),
            itemIngredientsList: document.getElementById('itemIngredientsList'),
        },

        // State
        state: {
            items: [],
            categories: [],
            ingredients: [], // All available ingredients
            currentRecipe: new Map(), // Map to store recipe for the item being edited {ingredientId: {qty, name, unit}}
            isDrawerOpen: false,
            filters: {
                searchTerm: '',
                categoryId: 'all',
            },
        },
        
        lazyLoader: null,

        // Initialization
        init() {
            this.initLazyLoader();
            this.bindEvents();
            this.loadData();
        },
        
        // Event Binding
        bindEvents() {
            this.elements.addItemBtn.addEventListener('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.addEventListener('click', () => this.closeDrawer());
            this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
            this.elements.imageInput.addEventListener('change', () => this.handleFilePreview());
            this.elements.searchInput.addEventListener('input', (e) => {
                this.state.filters.searchTerm = e.target.value.toLowerCase();
                this.render();
            });
            
            // --- Corrected Ingredient Event Handling ---
            this.elements.addIngredientBtn.addEventListener('click', () => this.addIngredientToRecipe());
            
            // Listen for clicks (for delete button)
            this.elements.itemIngredientsList.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-ingredient-btn');
                if (removeBtn) {
                    const ingredientId = parseInt(removeBtn.closest('.ingredient-row').dataset.ingredientId, 10);
                    if (!isNaN(ingredientId)) {
                        this.removeIngredientFromRecipe(ingredientId);
                    }
                }
            });

            // Listen for changes in quantity inputs
            this.elements.itemIngredientsList.addEventListener('change', (e) => {
                const qtyInput = e.target.closest('.ingredient-qty-input');
                if (qtyInput) {
                    const ingredientId = parseInt(qtyInput.closest('.ingredient-row').dataset.ingredientId, 10);
                    const newQty = parseFloat(qtyInput.value);
                    if (!isNaN(ingredientId)) {
                        this.updateIngredientQuantity(ingredientId, newQty);
                    }
                }
            });

            // Use event delegation for dynamically created elements
            this.elements.menuContent.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.edit-btn');
                const deleteBtn = e.target.closest('.delete-btn');
                if (editBtn) this.openDrawer(editBtn.dataset.id);
                if (deleteBtn) this.handleDelete(deleteBtn.dataset.id);
            });

            this.elements.categoryFilters.addEventListener('click', (e) => {
                const filterBtn = e.target.closest('.filter-btn');
                if (filterBtn) {
                    this.elements.categoryFilters.querySelector('.active')?.classList.remove('active');
                    filterBtn.classList.add('active');
                    this.state.filters.categoryId = filterBtn.dataset.categoryId;
                    this.render();
                }
            });
        },
        
        // Data Handling
        async loadData() {
            try {
                const [itemsRes, categoriesRes, ingredientsRes] = await Promise.all([
                    fetch('ajax/ajax_handler_menuitems.php?action=fetchAll'),
                    fetch('ajax/ajax_handler_categories.php?action=fetchAll'),
                    fetch('ajax/ajax_handler_ingredients.php?action=fetchAll')
                ]);
                const itemsData = await itemsRes.json();
                const categoriesData = await categoriesRes.json();
                const ingredientsData = await ingredientsRes.json();

                if (itemsData.success) this.state.items = itemsData.data;
                if (categoriesData.success) this.state.categories = categoriesData.data;
                if (ingredientsData.success) {
                    this.state.ingredients = ingredientsData.data;
                    this.populateIngredientSelect();
                }

                this.render();
                this.populateCategoryFilter();
            } catch (error) {
                console.error("Error loading data:", error);
                alert('Failed to load initial data. Please try again.');
            }
        },

        // Rendering Logic
        render() {
            this.elements.menuContent.innerHTML = '';
            
            let filteredItems = this.state.items;
            if (this.state.filters.categoryId !== 'all') {
                filteredItems = filteredItems.filter(item => item.CategoryID === this.state.filters.categoryId);
            }
            if (this.state.filters.searchTerm) {
                filteredItems = filteredItems.filter(item => 
                    item.Name.toLowerCase().includes(this.state.filters.searchTerm) ||
                    (item.Description && item.Description.toLowerCase().includes(this.state.filters.searchTerm))
                );
            }

            if (filteredItems.length === 0) {
                this.elements.emptyState.style.display = 'block';
                this.elements.menuContent.innerHTML = '';
                return;
            }
            this.elements.emptyState.style.display = 'none';

            const shouldGroup = this.state.filters.categoryId === 'all' && !this.state.filters.searchTerm;

            if (shouldGroup) {
                const itemsByCategory = filteredItems.reduce((acc, item) => {
                    const categoryId = item.CategoryID || 'uncategorized';
                    if (!acc[categoryId]) {
                        acc[categoryId] = { name: item.CategoryName || 'Uncategorized', items: [] };
                    }
                    acc[categoryId].items.push(item);
                    return acc;
                }, {});

                const sortedCategoryIds = this.state.categories.map(c => c.CategoryID).concat(['uncategorized']);
                sortedCategoryIds.forEach(categoryId => {
                    if (itemsByCategory[categoryId]) {
                        this.elements.menuContent.appendChild(this.createCategorySection(itemsByCategory[categoryId]));
                    }
                });
            } else {
                const grid = document.createElement('div');
                grid.className = 'items-grid';
                grid.innerHTML = filteredItems.map(item => this.createItemCard(item)).join('');
                this.elements.menuContent.appendChild(grid);
            }
            
            this.observeImages();
        },

        createCategorySection(category) {
            const section = document.createElement('section');
            section.className = 'category-section';
            section.id = `category-${category.id}`;
            section.innerHTML = `
                <h3 class="category-header">${this.escapeHTML(category.name)}</h3>
                <div class="items-grid">
                    ${category.items.map(item => this.createItemCard(item)).join('')}
                </div>
            `;
            return section;
        },

        createItemCard(item) {
            const imageUrl = item.ImageUrl || 'https://via.placeholder.com/300/e5e7eb/6b7280?text=No+Image';
            const isAvailable = item.IsAvailable == 1;
            return `
                <div class="menu-item-card">
                    <div class="card-image-wrapper">
                         <img data-src="${this.escapeHTML(imageUrl)}" alt="${this.escapeHTML(item.Name)}" class="card-img">
                         <div class="card-actions">
                            <button class="edit-btn" data-id="${item.MenuItemID}" title="Edit"><span class="material-icons-outlined">edit</span></button>
                            <button class="delete-btn" data-id="${item.MenuItemID}" title="Delete"><span class="material-icons-outlined">delete</span></button>
                        </div>
                    </div>
                    <div class="card-content">
                        <p class="card-category">${this.escapeHTML(item.CategoryName || 'N/A')}</p>
                        <h4 class="card-title">${this.escapeHTML(item.Name)}</h4>
                        <div class="card-footer">
                            <p class="card-price">${parseFloat(item.Price).toFixed(2)}</p>
                            <span class="availability-badge ${isAvailable ? 'available' : 'not-available'}">
                                ${isAvailable ? 'Available' : 'Unavailable'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        },

        // Drawer Management
        openDrawer(itemId = null) {
            this.elements.form.reset();
            this.resetImagePreview();
            this.populateCategorySelect();
            this.state.currentRecipe.clear();
            this.renderRecipe();
            
            if (itemId) {
                document.getElementById('drawerTitle').textContent = 'Edit Menu Item';
                this.fetchAndFillForm(itemId);
            } else {
                document.getElementById('drawerTitle').textContent = 'Add New Item';
                document.getElementById('menuItemId').value = '';
                document.getElementById('existingImagePath').value = '';
                document.getElementById('isAvailable').checked = true;
            }
            
            this.elements.drawer.classList.add('is-open');
            this.state.isDrawerOpen = true;
        },

        closeDrawer() {
            this.elements.drawer.classList.remove('is-open');
            this.state.isDrawerOpen = false;
        },

        // Form & Data Submission
        async fetchAndFillForm(id) {
            try {
                const item = this.state.items.find(i => i.MenuItemID == id);
                if (!item) throw new Error('Item not found in local state.');

                document.getElementById('menuItemId').value = item.MenuItemID;
                document.getElementById('itemName').value = item.Name;
                document.getElementById('itemPrice').value = item.Price;
                document.getElementById('itemDescription').value = item.Description;
                document.getElementById('isAvailable').checked = (item.IsAvailable == 1);
                document.getElementById('existingImagePath').value = item.RelativeImagePath || '';
                this.populateCategorySelect(item.CategoryID);
                
                if (item.ImageUrl) {
                    this.elements.imagePreview.src = item.ImageUrl;
                    this.elements.imagePreview.classList.add('has-image');
                    this.elements.imageUploadPlaceholder.style.opacity = '0';
                }

                const response = await fetch(`ajax/ajax_handler_menuitems.php?action=getMenuItemIngredients&id=${id}`);
                const recipeData = await response.json();
                if (recipeData.success) {
                    recipeData.data.forEach(ing => {
                        const ingredientDetails = this.state.ingredients.find(i => i.IngredientID == ing.IngredientID);
                        if (ingredientDetails) {
                            this.state.currentRecipe.set(parseInt(ing.IngredientID, 10), {
                                qty: parseFloat(ing.QuantityRequired),
                                name: ingredientDetails.Name,
                                unit: ingredientDetails.UnitOfMeasure
                            });
                        }
                    });
                    this.renderRecipe();
                }

            } catch (error) {
                console.error('Error fetching item:', error);
                alert('Could not load item data for editing.');
                this.closeDrawer();
            }
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            formData.append('action', 'save');

            const recipe = [];
            this.state.currentRecipe.forEach((details, id) => {
                recipe.push({ ingredient_id: id, quantity: details.qty });
            });
            formData.append('ingredients', JSON.stringify(recipe));

            try {
                const response = await fetch('ajax/ajax_handler_menuitems.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.closeDrawer();
                    await this.loadData();
                    showToast(data.message, 'success');
                } else { throw new Error(data.message); }
            } catch (error) {
                console.error('Error saving item:', error);
                showToast(error.message, 'error');
            }
        },

        async handleDelete(id) {
            if (confirm('Are you sure you want to delete this menu item? This action cannot be undone.')) {
                try {
                    const response = await fetch('ajax/ajax_handler_menuitems.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=delete&id=${id}`
                    });
                    const data = await response.json();
                    if (data.success) {
                        await this.loadData();
                        showToast(data.message, 'success');
                    } else { throw new Error(data.message); }
                } catch (error) {
                    console.error('Error deleting item:', error);
                    showToast(error.message, 'error');
                }
            }
        },

        // --- Corrected Ingredient-specific methods ---
        addIngredientToRecipe() {
            const ingredientId = parseInt(this.elements.ingredientListSelect.value, 10);
            const quantity = parseFloat(this.elements.ingredientQtyInput.value);

            if (!ingredientId || isNaN(quantity) || quantity <= 0) {
                alert('Please select an ingredient and enter a valid quantity.');
                return;
            }
            
            if (this.state.currentRecipe.has(ingredientId)) {
                alert('This ingredient is already in the recipe. You can edit its quantity directly in the list.');
                return;
            }

            const ingredient = this.state.ingredients.find(i => i.IngredientID == ingredientId);
            if (ingredient) {
                this.state.currentRecipe.set(ingredientId, {
                    qty: quantity,
                    name: ingredient.Name,
                    unit: ingredient.UnitOfMeasure
                });
                this.renderRecipe();
                this.elements.ingredientQtyInput.value = '';
            }
        },

        removeIngredientFromRecipe(ingredientId) {
            this.state.currentRecipe.delete(ingredientId);
            this.renderRecipe();
            showToast('Ingredient removed. Save the item to make it permanent.', 'info');
        },

        updateIngredientQuantity(ingredientId, newQty) {
            if (this.state.currentRecipe.has(ingredientId)) {
                if (isNaN(newQty) || newQty <= 0) {
                    this.state.currentRecipe.delete(ingredientId);
                    showToast('Invalid quantity. Ingredient has been removed.', 'error');
                } else {
                    this.state.currentRecipe.get(ingredientId).qty = newQty;
                }
                this.renderRecipe();
            }
        },

        renderRecipe() {
            this.elements.itemIngredientsList.innerHTML = '';
            if (this.state.currentRecipe.size === 0) {
                this.elements.itemIngredientsList.innerHTML = '<p style="text-align: center; color: var(--text-secondary); font-size: 0.9rem;">No ingredients added yet.</p>';
                return;
            }

            this.state.currentRecipe.forEach((details, id) => {
                const row = document.createElement('div');
                row.className = 'ingredient-row';
                row.dataset.ingredientId = id;
                row.innerHTML = `
                    <span class="ingredient-name">${this.escapeHTML(details.name)}</span>
                    <input type="number" class="ingredient-qty-input" value="${details.qty}" step="0.001" min="0.001" style="width: 80px; text-align: right; padding: 4px 8px; border-radius: 5px; border: 1px solid var(--border-color);">
                    <span class="ingredient-unit">${this.escapeHTML(details.unit)}</span>
                    <button type="button" class="remove-ingredient-btn" title="Remove">
                        <span class="material-icons-outlined">delete_forever</span>
                    </button>
                `;
                this.elements.itemIngredientsList.appendChild(row);
            });
        },

        // UI Helpers
        populateCategorySelect(selectedId = null) {
            const select = document.getElementById('itemCategory');
            select.innerHTML = '<option value="">-- Select a Category --</option>';
            this.state.categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.CategoryID;
                option.textContent = cat.CategoryName;
                if (cat.CategoryID == selectedId) option.selected = true;
                select.appendChild(option);
            });
        },

        populateIngredientSelect() {
            const select = this.elements.ingredientListSelect;
            select.innerHTML = '<option value="">-- Select an Ingredient --</option>';
            this.state.ingredients.forEach(ing => {
                const option = document.createElement('option');
                option.value = ing.IngredientID;
                option.textContent = `${ing.Name} (${ing.UnitOfMeasure})`;
                select.appendChild(option);
            });
        },

        populateCategoryFilter() {
            let filterHtml = '<button class="filter-btn active" data-category-id="all">All Items</button>';
            this.state.categories.forEach(cat => {
                filterHtml += `<button class="filter-btn" data-category-id="${cat.CategoryID}">${this.escapeHTML(cat.CategoryName)}</button>`;
            });
            this.elements.categoryFilters.innerHTML = filterHtml;
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
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: "0px 0px 200px 0px" });
        },

        observeImages() {
            const images = this.elements.menuContent.querySelectorAll('img[data-src]');
            images.forEach(img => this.lazyLoader.observe(img));
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    };
    
    MenuApp.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>
