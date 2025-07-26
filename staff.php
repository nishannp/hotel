<?php 
// staff.php
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
    --success-color: #10b981;
    --danger-color: #ef4444;
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

/* Staff Card Grid */
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.staff-card { background-color: var(--bg-content); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: box-shadow 0.3s ease, transform 0.3s ease; display: flex; flex-direction: column; text-align: center; padding: 1.5rem; }
.staff-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
.card-avatar-wrapper { width: 120px; height: 120px; margin: 0 auto 1rem; position: relative; }
.card-avatar { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid var(--bg-content); box-shadow: var(--shadow-md); }
.status-badge { position: absolute; bottom: 5px; right: 5px; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; }
.status-badge.active { background-color: var(--success-color); }
.status-badge.inactive { background-color: #9ca3af; }
.card-name { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin: 0; }
.card-role { font-size: 0.95rem; color: var(--primary-color); font-weight: 500; margin-bottom: 1rem; }
.card-contact { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; }
.card-actions { display: flex; justify-content: center; gap: 0.5rem; margin-top: auto; }
.card-actions button { background-color: var(--bg-main); color: var(--text-secondary); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
.card-actions button:hover { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }

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
.drawer-body input, .drawer-body select { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-main); transition: all 0.2s ease; }
.drawer-body input:focus, .drawer-body select:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); }
.checkbox-group { display: flex; align-items: center; gap: 10px; background-color: var(--bg-main); padding: 12px; border-radius: 8px; }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); flex-shrink: 0; background-color: var(--bg-content); }
.drawer-footer button { width: 100%; padding: 14px 22px; }
#imageUploadArea { width: 150px; height: 150px; margin: 0 auto 1rem; border: 2px dashed var(--border-color); border-radius: 50%; cursor: pointer; transition: border-color 0.2s; position: relative; overflow: hidden; background-color: #fcfcfd; }
#imageUploadArea:hover { border-color: var(--primary-color); }
#imagePreview { width: 100%; height: 100%; object-fit: cover; position: absolute; opacity: 0; transition: opacity 0.3s ease; }
#imagePreview.has-image { opacity: 1; }
#imageUploadPlaceholder { color: var(--text-secondary); text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; }
#imageUploadPlaceholder .material-icons-outlined { font-size: 48px; color: #cbd5e1; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2 id="main-title">Staff</h2>
        <button id="addItemBtn" class="btn-primary">
            <span class="material-icons-outlined">person_add</span>
            Add Staff
        </button>
    </div>

    <div class="filters-bar">
        <div class="search-bar">
            <span class="material-icons-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search by name or role...">
        </div>
    </div>

    <div id="itemsGrid" class="items-grid"></div>
    
    <div id="emptyState" class="empty-state">
        <span class="material-icons-outlined">people</span>
        <h3>No Staff Found</h3>
        <p>Try adjusting your search or add a new staff member.</p>
    </div>
</div>

<!-- Add/Edit Staff Drawer -->
<aside id="itemDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Staff</h3>
        <button class="close-drawer-btn" title="Close"><span class="material-icons-outlined">close</span></button>
    </div>
    <form id="itemForm" class="drawer-body" enctype="multipart/form-data">
        <input type="hidden" id="staffId" name="staff_id">
        <input type="hidden" id="existingImagePath" name="existing_image_path">
        
        <div class="form-group">
            <div id="imageUploadArea" onclick="document.getElementById('itemImage').click();">
                <img id="imagePreview" src="#">
                <div id="imageUploadPlaceholder">
                    <span class="material-icons-outlined">add_a_photo</span>
                    <p style="font-size: 0.8rem; margin-top: 0.5rem;">Upload Photo</p>
                </div>
            </div>
            <input type="file" id="itemImage" name="image" accept="image/*" style="display:none;">
        </div>

        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="last_name" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Waiter">Waiter</option>
                <option value="Chef">Chef</option>
                <option value="Manager">Manager</option>
                <option value="Cashier">Cashier</option>
            </select>
        </div>
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phone_number" required>
        </div>
        <div class="form-group">
            <label for="hireDate">Hire Date</label>
            <input type="date" id="hireDate" name="hire_date" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <div class="checkbox-group">
                 <input type="checkbox" id="isActive" name="is_active" checked>
                 <label for="isActive" style="margin-bottom: 0; cursor: pointer;">This staff member is currently active</label>
            </div>
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="itemForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Staff Member
        </button>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Staff Management';

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
                if (editBtn) this.openDrawer(editBtn.dataset.id);
            });
        },
        
        async loadData() {
            try {
                const response = await fetch('ajax/ajax_handler_staff.php?action=fetchAll');
                const data = await response.json();
                this.state.items = data.success ? data.data : [];
                this.render();
            } catch (error) {
                console.error("Error loading data:", error);
                alert('Failed to load staff data.');
            }
        },

        render() {
            this.elements.grid.innerHTML = '';
            const filteredItems = this.state.items.filter(item => 
                `${item.FirstName} ${item.LastName}`.toLowerCase().includes(this.state.searchTerm) ||
                item.Role.toLowerCase().includes(this.state.searchTerm)
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
            const defaultAvatar = 'https://via.placeholder.com/150/d1d5db/1f2937?text=' + this.escapeHTML(item.FirstName.charAt(0));
            const imageUrl = item.ImageUrl || defaultAvatar;
            const isActive = item.IsActive == 1;

            return `
                <div class="staff-card">
                    <div class="card-avatar-wrapper">
                        <img data-src="${imageUrl}" alt="${this.escapeHTML(item.FirstName)}" class="card-avatar">
                        <span class="status-badge ${isActive ? 'active' : 'inactive'}" title="${isActive ? 'Active' : 'Inactive'}"></span>
                    </div>
                    <h3 class="card-name">${this.escapeHTML(item.FirstName)} ${this.escapeHTML(item.LastName)}</h3>
                    <p class="card-role">${this.escapeHTML(item.Role)}</p>
                    <p class="card-contact">${this.escapeHTML(item.PhoneNumber)}</p>
                    <div class="card-actions">
                        <button class="edit-btn" data-id="${item.StaffID}">Edit Details</button>
                    </div>
                </div>
            `;
        },

        openDrawer(itemId = null) {
            this.elements.form.reset();
            this.resetImagePreview();
            
            if (itemId) {
                document.getElementById('drawerTitle').textContent = 'Edit Staff Member';
                const item = this.state.items.find(i => i.StaffID == itemId);
                if (item) {
                    document.getElementById('staffId').value = item.StaffID;
                    document.getElementById('firstName').value = item.FirstName;
                    document.getElementById('lastName').value = item.LastName;
                    document.getElementById('role').value = item.Role;
                    document.getElementById('phoneNumber').value = item.PhoneNumber;
                    document.getElementById('hireDate').value = item.HireDate;
                    document.getElementById('isActive').checked = (item.IsActive == 1);
                    document.getElementById('existingImagePath').value = item.RelativeImagePath || '';
                    if (item.ImageUrl) {
                        this.elements.imagePreview.src = item.ImageUrl;
                        this.elements.imagePreview.classList.add('has-image');
                        this.elements.imageUploadPlaceholder.style.opacity = '0';
                    }
                }
            } else {
                document.getElementById('drawerTitle').textContent = 'Add New Staff Member';
                document.getElementById('staffId').value = '';
                document.getElementById('existingImagePath').value = '';
                document.getElementById('isActive').checked = true;
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
                const response = await fetch('ajax/ajax_handler_staff.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.closeDrawer();
                    await this.loadData();
                } else { throw new Error(data.message); }
            } catch (error) {
                console.error('Error saving item:', error);
                alert('Error: ' + error.message);
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
                        img.onload = () => img.style.opacity = 1;
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
