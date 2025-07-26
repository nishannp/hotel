<?php 
// customers.php
require_once 'includes/header.php'; 
?>

<!-- Google Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

<style>
/* Using the same clean, light theme from other pages */
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

/* Base & Layout */
.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.content-header h1 { color: var(--text-primary) !important; font-weight: 600 !important; }
.page-container { padding: 2rem; }

/* Header & Filters */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.75rem; font-weight: 600; color: var(--text-primary); }
.btn-primary { background-color: var(--primary-color); color: white; border: none; padding: 12px 22px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px; transition: background-color 0.2s ease, transform 0.1s ease; box-shadow: var(--shadow-sm); }
.btn-primary:hover { background-color: var(--primary-hover); box-shadow: var(--shadow-md); }
.btn-primary:active { transform: scale(0.98); }
.btn-primary .material-icons-outlined { font-size: 20px; }

.filters-bar { display: flex; gap: 1rem; align-items: center; padding: 0.75rem; background-color: var(--bg-content); border-radius: 10px; margin-bottom: 2rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); flex-wrap: wrap; }
.search-bar { display: flex; align-items: center; background-color: var(--bg-main); border-radius: 8px; padding: 0 8px; flex-grow: 1; min-width: 250px; }
.search-bar .material-icons-outlined { color: var(--text-secondary); }
.search-bar input { border: none; background: transparent; padding: 10px; width: 100%; font-size: 0.95rem; }
.search-bar input:focus { outline: none; }

/* Customer Card Grid */
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.customer-card { background-color: var(--bg-content); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: box-shadow 0.3s ease, transform 0.3s ease; display: flex; flex-direction: column; }
.customer-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
.card-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; }
.avatar { width: 50px; height: 50px; border-radius: 50%; background-color: var(--primary-color); color: white; display: grid; place-items: center; font-size: 1.5rem; font-weight: 500; flex-shrink: 0; }
.card-name { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); }
.card-body { padding: 0 1.5rem 1.5rem; color: var(--text-secondary); }
.contact-info { display: flex; flex-direction: column; gap: 0.5rem; }
.contact-item { display: flex; align-items: center; gap: 0.75rem; }
.contact-item .material-icons-outlined { font-size: 20px; }
.card-footer { border-top: 1px solid var(--border-color); padding: 0.75rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem; background-color: #f9fafb; }
.card-footer button { background-color: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; }
.card-footer button:hover { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }
.card-footer button .material-icons-outlined { font-size: 18px; }

/* Empty State */
.empty-state { display: none; text-align: center; padding: 4rem 2rem; background-color: var(--bg-content); border: 2px dashed var(--border-color); border-radius: 12px; color: var(--text-secondary); }
.empty-state .material-icons-outlined { font-size: 64px; color: var(--primary-color); opacity: 0.5; }
.empty-state h3 { margin-top: 1rem; font-size: 1.5rem; color: var(--text-primary); }

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
.drawer-body input { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-main); transition: all 0.2s ease; }
.drawer-body input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); flex-shrink: 0; background-color: var(--bg-content); }
.drawer-footer button { width: 100%; padding: 14px 22px; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2 id="main-title">Customers</h2>
        <button id="addItemBtn" class="btn-primary">
            <span class="material-icons-outlined">person_add</span>
            Add Customer
        </button>
    </div>

    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search by name, phone, or email...">
        </div>
    </div>

    <div id="itemsGrid" class="items-grid"></div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">people</span>
        <h3>No Customers Found</h3>
        <p>Try adjusting your search or add a new customer.</p>
    </div>
</div>

<!-- Add/Edit Customer Drawer -->
<aside id="itemDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Customer</h3>
        <button class="close-drawer-btn" title="Close"><span class="material-icons-outlined">close</span></button>
    </div>
    <form id="itemForm" class="drawer-body">
        <input type="hidden" id="customerId" name="customer_id">
        
        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name (Optional)</label>
            <input type="text" id="lastName" name="last_name">
        </div>
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phone_number" required>
        </div>
        <div class="form-group">
            <label for="email">Email (Optional)</label>
            <input type="email" id="email" name="email">
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="itemForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Customer
        </button>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Customer Management';

    const App = {
        elements: {
            drawer: document.getElementById('itemDrawer'),
            form: document.getElementById('itemForm'),
            grid: document.getElementById('itemsGrid'),
            emptyState: document.getElementById('emptyState'),
            addItemBtn: document.getElementById('addItemBtn'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            searchInput: document.getElementById('searchInput'),
        },
        state: {
            items: [],
            searchTerm: '',
        },

        init() {
            this.bindEvents();
            this.loadData();
        },
        
        bindEvents() {
            this.elements.addItemBtn.addEventListener('click', () => this.openDrawer());
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
        },
        
        async loadData() {
            try {
                const response = await fetch('ajax/ajax_handler_customers.php?action=fetchAll');
                const data = await response.json();
                this.state.items = data.success ? data.data : [];
                this.render();
            } catch (error) {
                console.error("Error loading data:", error);
                showToast('Failed to load customer data.', 'error');
            }
        },

        render() {
            this.elements.grid.innerHTML = '';
            const filteredItems = this.state.items.filter(item => {
                const fullName = `${item.FirstName} ${item.LastName}`.toLowerCase();
                const phone = item.PhoneNumber || '';
                const email = item.Email || '';
                return fullName.includes(this.state.searchTerm) ||
                       phone.includes(this.state.searchTerm) ||
                       email.toLowerCase().includes(this.state.searchTerm);
            });

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
        },

        createItemCard(item) {
            const initial = this.escapeHTML(item.FirstName.charAt(0).toUpperCase());
            const fullName = `${this.escapeHTML(item.FirstName)} ${this.escapeHTML(item.LastName)}`;
            return `
                <div class="customer-card">
                    <div class="card-header">
                        <div class="avatar">${initial}</div>
                        <div>
                            <h3 class="card-name">${fullName}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="contact-info">
                            <div class="contact-item">
                                <span class="material-icons-outlined">phone</span>
                                <span>${this.escapeHTML(item.PhoneNumber) || 'N/A'}</span>
                            </div>
                            <div class="contact-item">
                                <span class="material-icons-outlined">email</span>
                                <span>${this.escapeHTML(item.Email) || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="edit-btn" data-id="${item.CustomerID}">
                            <span class="material-icons-outlined">edit</span> Edit
                        </button>
                        <button class="delete-btn" data-id="${item.CustomerID}">
                            <span class="material-icons-outlined">delete</span> Delete
                        </button>
                    </div>
                </div>
            `;
        },

        openDrawer(itemId = null) {
            this.elements.form.reset();
            
            if (itemId) {
                document.getElementById('drawerTitle').textContent = 'Edit Customer';
                const item = this.state.items.find(i => i.CustomerID == itemId);
                if (item) {
                    document.getElementById('customerId').value = item.CustomerID;
                    document.getElementById('firstName').value = item.FirstName;
                    document.getElementById('lastName').value = item.LastName;
                    document.getElementById('phoneNumber').value = item.PhoneNumber;
                    document.getElementById('email').value = item.Email;
                }
            } else {
                document.getElementById('drawerTitle').textContent = 'Add New Customer';
                document.getElementById('customerId').value = '';
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
                const response = await fetch('ajax/ajax_handler_customers.php', { method: 'POST', body: formData });
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
            if (confirm('Are you sure you want to delete this customer?')) {
                try {
                    const response = await fetch('ajax/ajax_handler_customers.php', {
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
