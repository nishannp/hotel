<?php 
// ingredients.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<style>
/* Using the same clean, light theme from menu_items.php */
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --warning-color: #f59e0b;
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
.page-container {
    padding: 2rem;
}

/* Page Header & Filters */
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
.btn-primary:active { transform: scale(0.98); }
.btn-primary .material-icons-outlined { font-size: 20px; }

.filters-bar {
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
.search-bar .material-icons-outlined { color: var(--text-secondary); }
.search-bar input {
    border: none;
    background: transparent;
    padding: 10px;
    width: 100%;
    font-size: 0.95rem;
}
.search-bar input:focus { outline: none; }

/* Item Grid & Card */
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}
.item-card {
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
.item-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.card-image-wrapper {
    width: 100%;
    height: 190px;
    position: relative;
    background-color: #f3f4f6;
}
.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.4s ease;
    opacity: 0;
}
.card-img.loaded { opacity: 1; }
.card-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 8px;
}
.card-actions button {
    width: 36px; height: 36px;
    border-radius: 50%; border: none;
    background-color: rgba(255, 255, 255, 0.9);
    color: var(--text-primary);
    cursor: pointer;
    display: grid; place-items: center;
    box-shadow: var(--shadow-md);
    transition: transform 0.2s;
}
.card-actions button:hover { transform: scale(1.1); }
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
.stock-level {
    font-size: 1.35rem;
    font-weight: 700;
    margin-top: auto;
    padding-top: 1rem;
}
.stock-level .unit {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-secondary);
    margin-left: 4px;
}
.stock-status-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
}
.stock-in { background-color: var(--success-color); }
.stock-low { background-color: var(--warning-color); }
.stock-out { background-color: var(--danger-color); }

/* Empty State */
.empty-state {
    display: none;
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

/* Drawer Styles are identical to menu_items.php, so they are omitted for brevity but will be applied */
.form-drawer { position: fixed; top: 0; right: 0; width: var(--drawer-width); height: 100%; background-color: var(--bg-content); box-shadow: -10px 0 30px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); z-index: 1050; display: flex; flex-direction: column; }
.form-drawer.is-open { transform: translateX(0); }
.drawer-header { padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
#drawerTitle { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); }
.close-drawer-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; color: var(--text-secondary); transition: all 0.2s ease; }
.close-drawer-btn:hover { background: #e5e7eb; color: var(--text-primary); transform: rotate(90deg); }
.drawer-body { padding: 2rem; overflow-y: auto; flex-grow: 1; }
.drawer-body .form-group { margin-bottom: 1.5rem; }
.drawer-body label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary); font-size: 0.9rem; }
.drawer-body input[type="text"], .drawer-body input[type="number"], .drawer-body select, .drawer-body textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-main); transition: all 0.2s ease; }
.drawer-body input:focus, .drawer-body select:focus, .drawer-body textarea:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); }
.drawer-body textarea { resize: vertical; min-height: 100px; }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); flex-shrink: 0; background-color: var(--bg-content); }
.drawer-footer button { width: 100%; padding: 14px 22px; }
#imageUploadArea { width: 100%; height: 200px; border: 2px dashed var(--border-color); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: border-color 0.2s; position: relative; overflow: hidden; background-color: #fcfcfd; }
#imageUploadArea:hover { border-color: var(--primary-color); }
#imagePreview { width: 100%; height: 100%; object-fit: cover; position: absolute; opacity: 0; transition: opacity 0.3s ease; }
#imagePreview.has-image { opacity: 1; }
#imageUploadPlaceholder { color: var(--text-secondary); text-align: center; }
#imageUploadPlaceholder .material-icons-outlined { font-size: 48px; color: #cbd5e1; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2 id="main-title">Inventory</h2>
        <button id="addItemBtn" class="btn-primary">
            <span class="material-icons-outlined">add_circle</span>
            Add Ingredient
        </button>
    </div>

    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search for ingredients...">
        </div>
    </div>

    <div id="itemsGrid" class="items-grid">
        <!-- Ingredient cards will be dynamically inserted here -->
    </div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">inventory_2</span>
        <h3>No Ingredients Found</h3>
        <p>Try adjusting your search or add a new ingredient to get started.</p>
    </div>
</div>

<!-- Add/Edit Ingredient Drawer -->
<aside id="itemDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Ingredient</h3>
        <button class="close-drawer-btn" title="Close">
            <span class="material-icons-outlined">close</span>
        </button>
    </div>
    <form id="itemForm" class="drawer-body" enctype="multipart/form-data">
        <input type="hidden" id="ingredientId" name="ingredient_id">
        <input type="hidden" id="existingImagePath" name="existing_image_path">
        
        <div class="form-group">
            <div id="imageUploadArea" onclick="document.getElementById('itemImage').click();">
                <img id="imagePreview" src="#">
                <div id="imageUploadPlaceholder">
                    <span class="material-icons-outlined">cloud_upload</span>
                    <p style="font-weight: 500; margin-top: 0.5rem;">Click to upload image</p>
                </div>
            </div>
            <input type="file" id="itemImage" name="image" accept="image/*" style="display:none;">
        </div>

        <div class="form-group">
            <label for="name">Ingredient Name</label>
            <input type="text" id="name" name="name" required placeholder="e.g., All-Purpose Flour">
        </div>
        <div class="form-group">
            <label for="quantityInStock">Quantity In Stock</label>
            <input type="number" id="quantityInStock" name="quantity_in_stock" step="0.01" required placeholder="e.g., 10.5">
        </div>
        <div class="form-group">
            <label for="unitOfMeasure">Unit of Measure</label>
            <input type="text" id="unitOfMeasure" name="unit_of_measure" required placeholder="e.g., kg, liter, piece">
        </div>
        <div class="form-group">
            <label for="supplierInfo">Supplier Info (Optional)</label>
            <textarea id="supplierInfo" name="supplier_info" rows="3" placeholder="e.g., Local Farm Co., contact@lf.com"></textarea>
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="itemForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Ingredient
        </button>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Ingredient Management';

    const App = {
        elements: {
            drawer: document.getElementById('itemDrawer'),
            form: document.getElementById('itemForm'),
            grid: document.getElementById('itemsGrid'),
            emptyState: document.getElementById('emptyState'),
            addItemBtn: document.getElementById('addItemBtn'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            imageInput: document.getElementById('itemImage'),
            imagePreview: document.getElementById('imagePreview'),
            imageUploadPlaceholder: document.getElementById('imageUploadPlaceholder'),
            searchInput: document.getElementById('searchInput'),
        },
        state: {
            items: [],
            searchTerm: '',
        },
        lazyLoader: null,

        init() {
            this.initLazyLoader();
            this.bindEvents();
            this.loadData();
        },
        
        bindEvents() {
            this.elements.addItemBtn.addEventListener('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.addEventListener('click', () => this.closeDrawer());
            this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
            this.elements.imageInput.addEventListener('change', () => this.handleFilePreview());
            this.elements.searchInput.addEventListener('input', (e) => {
                this.state.searchTerm = e.target.value.toLowerCase();
                this.render();
            });
            this.elements.grid.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.edit-btn');
                const deleteBtn = e.target.closest('.delete-btn');
                if (editBtn) this.openDrawer(editBtn.dataset.id);
                if (deleteBtn) this.handleDelete(deleteBtn.dataset.id);
            });
        },
        
        async loadData() {
            try {
                const response = await fetch('ajax/ajax_handler_ingredients.php?action=fetchAll');
                const data = await response.json();
                this.state.items = data.success ? data.data : [];
                this.render();
            } catch (error) {
                console.error("Error loading data:", error);
                showToast('Failed to load ingredients.', 'error');
            }
        },

        render() {
            this.elements.grid.innerHTML = '';
            const filteredItems = this.state.items.filter(item => 
                item.Name.toLowerCase().includes(this.state.searchTerm)
            );

            if (filteredItems.length === 0) {
                this.elements.emptyState.style.display = 'block';
                this.elements.grid.style.display = 'none';
            } else {
                this.elements.emptyState.style.display = 'none';
                this.elements.grid.style.display = 'grid';
                filteredItems.forEach(item => {
                    this.elements.grid.insertAdjacentHTML('beforeend', this.createItemCard(item));
                });
            }
            this.observeImages();
        },

        createItemCard(item) {
            const imageUrl = item.ImageUrl || 'https://via.placeholder.com/300/e5e7eb/6b7280?text=No+Image';
            const quantity = parseFloat(item.QuantityInStock) || 0;
            let stockClass = 'stock-in';
            if (quantity === 0) stockClass = 'stock-out';
            else if (quantity < 10) stockClass = 'stock-low'; // Example threshold for low stock

            return `
                <div class="item-card">
                    <div class="card-image-wrapper">
                        <img data-src="${this.escapeHTML(imageUrl)}" alt="${this.escapeHTML(item.Name)}" class="card-img">
                    </div>
                    <div class="card-actions">
                        <button class="edit-btn" data-id="${item.IngredientID}" title="Edit"><span class="material-icons-outlined">edit</span></button>
                        <button class="delete-btn" data-id="${item.IngredientID}" title="Delete"><span class="material-icons-outlined">delete</span></button>
                    </div>
                    <div class="card-content">
                        <h4 class="card-title">${this.escapeHTML(item.Name)}</h4>
                        <div class="stock-level">
                            <span class="stock-status-dot ${stockClass}"></span>
                            ${quantity.toFixed(2)}
                            <span class="unit">${this.escapeHTML(item.UnitOfMeasure)}</span>
                        </div>
                    </div>
                </div>
            `;
        },

        openDrawer(itemId = null) {
            this.elements.form.reset();
            this.resetImagePreview();
            
            if (itemId) {
                document.getElementById('drawerTitle').textContent = 'Edit Ingredient';
                const item = this.state.items.find(i => i.IngredientID == itemId);
                if (item) {
                    document.getElementById('ingredientId').value = item.IngredientID;
                    document.getElementById('name').value = item.Name;
                    document.getElementById('quantityInStock').value = item.QuantityInStock;
                    document.getElementById('unitOfMeasure').value = item.UnitOfMeasure;
                    // We need to fetch supplier info for single edit
                    fetch(`ajax/ajax_handler_ingredients.php`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=fetchSingle&id=${itemId}`
                    }).then(res => res.json()).then(data => {
                        if(data.success) document.getElementById('supplierInfo').value = data.data.SupplierInfo;
                    });

                    document.getElementById('existingImagePath').value = item.RelativeImagePath || '';
                    if (item.ImageUrl) {
                        this.elements.imagePreview.src = item.ImageUrl;
                        this.elements.imagePreview.classList.add('has-image');
                        this.elements.imageUploadPlaceholder.style.opacity = '0';
                    }
                }
            } else {
                document.getElementById('drawerTitle').textContent = 'Add New Ingredient';
                document.getElementById('ingredientId').value = '';
                document.getElementById('existingImagePath').value = '';
            }
            this.elements.drawer.classList.add('is-open');
        },

        closeDrawer() {
            this.elements.drawer.classList.remove('is-open');
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            formData.append('action', 'save');

            try {
                const response = await fetch('ajax/ajax_handler_ingredients.php', { method: 'POST', body: formData });
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
            if (confirm('Are you sure? Deleting an ingredient cannot be undone.')) {
                try {
                    const response = await fetch('ajax/ajax_handler_ingredients.php', {
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
            const images = this.elements.grid.querySelectorAll('img[data-src]');
            images.forEach(img => this.lazyLoader.observe(img));
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    };
    
    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>
