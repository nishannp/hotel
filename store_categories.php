<?php 
// store_categories.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<style>
/* Using the same modern theme from menu_items.php */
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --font-family: 'Poppins', sans-serif;
    --drawer-width: 450px;
}

.page-container {
    padding: 2rem;
    background-color: var(--bg-main);
    font-family: var(--font-family);
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
}
.search-bar {
    display: flex;
    align-items: center;
    background-color: var(--bg-main);
    border-radius: 8px;
    padding: 0 8px;
    flex-grow: 1;
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

/* Categories Grid & Card */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.category-card {
    background-color: var(--bg-content);
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}
.card-body {
    padding: 1.5rem;
    color: var(--text-secondary);
    flex-grow: 1;
}
.card-body p {
    margin: 0;
    line-height: 1.6;
}
.card-footer {
    padding: 1rem 1.5rem;
    background-color: #f9fafb;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.card-footer .action-btn {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.card-footer .action-btn .material-icons-outlined { font-size: 18px; }
.card-footer .action-btn.edit-btn:hover { background-color: #eef2ff; border-color: var(--primary-color); color: var(--primary-color); }
.card-footer .action-btn.delete-btn:hover { background-color: #fee2e2; border-color: #ef4444; color: #ef4444; }

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

/* Drawer Styles */
.form-drawer { position: fixed; top: 0; right: 0; width: var(--drawer-width); height: 100%; background-color: var(--bg-content); box-shadow: -10px 0 30px rgba(0,0,0,0.1); transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); z-index: 1050; display: flex; flex-direction: column; }
.form-drawer.is-open { transform: translateX(0); }
.drawer-header { padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
#drawerTitle { font-size: 1.5rem; font-weight: 600; color: var(--text-primary); }
.close-drawer-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; color: var(--text-secondary); transition: all 0.2s ease; }
.close-drawer-btn:hover { background: #e5e7eb; color: var(--text-primary); transform: rotate(90deg); }
.drawer-body { padding: 2rem; overflow-y: auto; flex-grow: 1; }
.drawer-body .form-group { margin-bottom: 1.5rem; }
.drawer-body label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-primary); font-size: 0.9rem; }
.drawer-body input[type="text"], .drawer-body textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-main); transition: all 0.2s ease; }
.drawer-body input:focus, .drawer-body textarea:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); }
.drawer-body textarea { resize: vertical; min-height: 120px; }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); flex-shrink: 0; background-color: var(--bg-content); }
.drawer-footer button { width: 100%; padding: 14px 22px; }

/* Confirmation Modal Styles */
.confirmation-modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 2000; /* Higher than the drawer */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}
.confirmation-modal.show {
    display: flex;
}
.confirmation-modal .modal-content {
    background-color: var(--bg-content);
    margin: auto;
    padding: 0;
    border: 1px solid var(--border-color);
    width: 90%;
    max-width: 450px;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    animation: fadeInScale 0.3s ease-out;
}
@keyframes fadeInScale {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.confirmation-modal .modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.confirmation-modal .modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}
.confirmation-modal .close-btn {
    color: var(--text-secondary);
    font-size: 28px;
    font-weight: bold;
    background: none;
    border: none;
    cursor: pointer;
}
.confirmation-modal .modal-body {
    padding: 1.5rem;
    line-height: 1.6;
    color: var(--text-secondary);
}
.confirmation-modal .modal-footer {
    padding: 1rem 1.5rem;
    background-color: #f9fafb;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}
.confirmation-modal .btn-secondary {
    background-color: #e5e7eb;
    color: var(--text-primary);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}
.confirmation-modal .btn-secondary:hover {
    background-color: #d1d5db;
}
.confirmation-modal .btn-danger {
    background-color: #ef4444;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}
.confirmation-modal .btn-danger:hover {
    background-color: #dc2626;
}
</style>

<div class="page-container">
    <div class="page-header">
        <h2 id="main-title">Store Categories</h2>
        <button id="addCategoryBtn" class="btn-primary">
            <span class="material-icons-outlined">add_circle</span>
            Add New Category
        </button>
    </div>

    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search for categories...">
        </div>
    </div>

    <div id="categoriesGrid" class="categories-grid">
        <!-- Category cards will be dynamically inserted here -->
    </div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">category</span>
        <h3>No Categories Found</h3>
        <p>Try adjusting your search or add a new category to get started.</p>
    </div>
</div>

<!-- Add/Edit Category Drawer -->
<aside id="categoryDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Category</h3>
        <button class="close-drawer-btn" title="Close">
            <span class="material-icons-outlined">close</span>
        </button>
    </div>
    <form id="categoryForm" class="drawer-body">
        <input type="hidden" id="categoryId" name="category_id">
        
        <div class="form-group">
            <label for="categoryName">Category Name</label>
            <input type="text" id="categoryName" name="category_name" required placeholder="e.g., Snacks, Drinks">
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="categoryForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Category
        </button>
    </div>
</aside>

<!-- Custom Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="confirmationTitle">Confirm Deletion</h3>
            <button id="closeConfirmationModalBtn" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmationMessage">Are you sure you want to delete this category? All items within it will also be permanently deleted.</p>
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
    document.querySelector('.content-header h1').textContent = 'Store Categories';

    const CategoriesApp = {
        elements: {
            drawer: document.getElementById('categoryDrawer'),
            form: document.getElementById('categoryForm'),
            grid: document.getElementById('categoriesGrid'),
            emptyState: document.getElementById('emptyState'),
            addBtn: document.getElementById('addCategoryBtn'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            searchInput: document.getElementById('searchInput'),
            drawerTitle: document.getElementById('drawerTitle'),
            categoryIdField: document.getElementById('categoryId'),
            // Confirmation Modal Elements
            confirmationModal: document.getElementById('confirmationModal'),
            confirmDeleteBtn: document.getElementById('confirmDeleteBtn'),
            cancelDeleteBtn: document.getElementById('cancelDeleteBtn'),
            closeConfirmationModalBtn: document.getElementById('closeConfirmationModalBtn'),
        },
        state: {
            categories: [],
            searchTerm: '',
            deleteCategoryId: null,
        },

        init() {
            this.bindEvents();
            this.loadCategories();
        },
        
        bindEvents() {
            this.elements.addBtn.addEventListener('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.addEventListener('click', () => this.closeDrawer());
            this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
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

            // Confirmation Modal Events
            this.elements.confirmDeleteBtn.addEventListener('click', () => this.executeDelete());
            this.elements.cancelDeleteBtn.addEventListener('click', () => this.hideConfirmationModal());
            this.elements.closeConfirmationModalBtn.addEventListener('click', () => this.hideConfirmationModal());
        },
        
        async loadCategories() {
            try {
                const response = await fetch('ajax/ajax_handler_store_categories.php?action=fetchAll');
                const data = await response.json();
                this.state.categories = data.success ? data.data : [];
                this.render();
            } catch (error) {
                console.error("Error loading categories:", error);
                this.showToast('Failed to load categories.', true);
            }
        },

        render() {
            this.elements.grid.innerHTML = '';
            const filtered = this.state.categories.filter(cat => 
                cat.CategoryName.toLowerCase().includes(this.state.searchTerm)
            );

            if (filtered.length === 0) {
                this.elements.emptyState.style.display = 'block';
                this.elements.grid.style.display = 'none';
            } else {
                this.elements.emptyState.style.display = 'none';
                this.elements.grid.style.display = 'grid';
                filtered.forEach(cat => {
                    this.elements.grid.insertAdjacentHTML('beforeend', this.createCategoryCard(cat));
                });
            }
        },

        createCategoryCard(cat) {
            return `
                <div class="category-card" data-id="${cat.CategoryID}">
                    <div class="card-header">
                        <h4 class="card-title">${this.escapeHTML(cat.CategoryName)}</h4>
                    </div>
                    <div class="card-body">
                        <p>A category for store items.</p>
                    </div>
                    <div class="card-footer">
                        <button class="action-btn edit-btn" data-id="${cat.CategoryID}">
                            <span class="material-icons-outlined">edit</span> Edit
                        </button>
                        <button class="action-btn delete-btn" data-id="${cat.CategoryID}">
                            <span class="material-icons-outlined">delete</span> Delete
                        </button>
                    </div>
                </div>
            `;
        },

        openDrawer(id = null) {
            this.elements.form.reset();
            if (id) {
                this.elements.drawerTitle.textContent = 'Edit Category';
                const category = this.state.categories.find(c => c.CategoryID == id);
                if (category) {
                    this.elements.categoryIdField.value = category.CategoryID;
                    document.getElementById('categoryName').value = category.CategoryName;
                }
            } else {
                this.elements.drawerTitle.textContent = 'Add New Category';
                this.elements.categoryIdField.value = '';
            }
            this.elements.drawer.classList.add('is-open');
        },

        closeDrawer() {
            this.elements.drawer.classList.remove('is-open');
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            formData.append('action', 'save'); // Always use 'save' action

            try {
                const response = await fetch('ajax/ajax_handler_store_categories.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.closeDrawer();
                    await this.loadCategories();
                    this.showToast(data.message);
                } else { throw new Error(data.message); }
            } catch (error) {
                this.showToast(error.message, true);
            }
        },

        handleDelete(id) {
            this.state.deleteCategoryId = id;
            this.showConfirmationModal();
        },

        async executeDelete() {
            if (!this.state.deleteCategoryId) return;
            
            try {
                const response = await fetch('ajax/ajax_handler_store_categories.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete&id=${this.state.deleteCategoryId}`
                });
                const data = await response.json();
                this.hideConfirmationModal();
                if (data.success) {
                    await this.loadCategories();
                    this.showToast(data.message);
                } else { throw new Error(data.message); }
            } catch (error) {
                this.showToast(error.message, true);
            } finally {
                this.state.deleteCategoryId = null;
            }
        },

        showConfirmationModal() {
            this.elements.confirmationModal.classList.add('show');
        },

        hideConfirmationModal() {
            this.elements.confirmationModal.classList.remove('show');
        },

        showToast(message, isError = false) {
            const toast = document.getElementById('toastNotification');
            toast.textContent = message;
            toast.className = 'toast-notification show';
            if (isError) {
                toast.classList.add('error');
            }
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    };
    
    CategoriesApp.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>