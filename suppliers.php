<?php 
require_once 'includes/header.php'; 
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<style>
:root {
    --bg-main: #f7f8fc;
    --bg-content: #ffffff;
    --primary-color: #6a5acd; /* Slate Blue */
    --primary-hover: #5a4bad;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --font-family: 'Poppins', sans-serif;
    --drawer-width: 450px;
}

.content-wrapper { background-color: var(--bg-main); font-family: var(--font-family); }
.page-container { padding: 2rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.75rem; font-weight: 600; }

.btn-primary {
    background-color: var(--primary-color); color: white; border: none; padding: 12px 22px;
    border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem;
    display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.btn-primary:hover { background-color: var(--primary-hover); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }

.table-container { background-color: var(--bg-content); border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); overflow: hidden; }
.table-header { padding: 1.5rem; border-bottom: 1px solid var(--border-color); }
.table-header input { width: 100%; max-width: 400px; padding: 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
th { background-color: #f9fafb; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase; }
tr:last-child td { border-bottom: none; }

/* Corrected Toggle Switch Style */
.switch { position: relative; display: inline-block; width: 50px; height: 28px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc; transition: .4s; border-radius: 28px;
}
.slider:before {
    position: absolute; content: ""; height: 20px; width: 20px;
    left: 4px; bottom: 4px; background-color: white;
    transition: .4s; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
input:checked + .slider { background-color: var(--success-color); }
input:focus + .slider { box-shadow: 0 0 1px var(--success-color); }
input:checked + .slider:before { transform: translateX(22px); }

.action-btns { display: flex; gap: 0.5rem; align-items: center; }
.action-btns button { background: transparent; border: none; cursor: pointer; padding: 5px; border-radius: 50%; transition: background-color 0.2s; }
.action-btns button:hover { background-color: #f0f0f0; }
.action-btns .material-icons-outlined { color: var(--text-secondary); }

/* Drawer */
.form-drawer {
    position: fixed; top: 0; right: 0; width: var(--drawer-width); height: 100%;
    background-color: var(--bg-content); box-shadow: -10px 0 30px rgba(0,0,0,0.1);
    transform: translateX(100%); transition: transform 0.4s ease;
    z-index: 1050; display: flex; flex-direction: column;
}
.form-drawer.is-open { transform: translateX(0); }
.drawer-header { padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
#drawerTitle { font-size: 1.5rem; font-weight: 600; }
.close-drawer-btn { background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; transition: all 0.2s ease; }
.close-drawer-btn:hover { background: #e5e7eb; transform: rotate(90deg); }
.drawer-body { padding: 2rem; overflow-y: auto; flex-grow: 1; }
.form-group { margin-bottom: 1.5rem; }
.drawer-body label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.9rem; }
.drawer-body input { width: 100%; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background-color: var(--bg-main); }
.drawer-body input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.2); }
.checkbox-group { display: flex; align-items: center; gap: 10px; background-color: var(--bg-main); padding: 12px; border-radius: 8px; }
.drawer-footer { padding: 1.5rem 2rem; border-top: 1px solid var(--border-color); }
.drawer-footer button { width: 100%; padding: 14px 22px; }
</style>

<div class="page-container">
    <div class="page-header">
        <h2>Supplier Management</h2>
        <button id="addBtn" class="btn-primary">
            <span class="material-icons-outlined">add_circle</span>
            Add New Supplier
        </button>
    </div>

    <div class="table-container">
        <div class="table-header">
            <input type="text" id="searchInput" placeholder="Search suppliers...">
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="suppliersTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Drawer -->
<aside id="formDrawer" class="form-drawer">
    <div class="drawer-header">
        <h3 id="drawerTitle">Add New Supplier</h3>
        <button class="close-drawer-btn" title="Close"><span class="material-icons-outlined">close</span></button>
    </div>
    <form id="supplierForm" class="drawer-body">
        <input type="hidden" id="supplierId" name="supplier_id">
        <div class="form-group">
            <label for="supplierName">Supplier Name</label>
            <input type="text" id="supplierName" name="supplier_name" required>
        </div>
        <div class="form-group">
            <label for="contactPerson">Contact Person</label>
            <input type="text" id="contactPerson" name="contact_person">
        </div>
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phone_number" required>
        </div>
        <div class="form-group" id="statusFormGroup">
            <label>Status</label>
            <div class="checkbox-group">
                 <input type="checkbox" id="isActive" name="is_active" checked>
                 <label for="isActive" style="margin-bottom: 0; cursor: pointer;">Supplier is Active</label>
            </div>
        </div>
    </form>
    <div class="drawer-footer">
        <button type="submit" form="supplierForm" class="btn-primary">
            <span class="material-icons-outlined">save</span>
            Save Supplier
        </button>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.content-header h1').textContent = 'Suppliers';

    const App = {
        elements: {
            addBtn: document.getElementById('addBtn'),
            drawer: document.getElementById('formDrawer'),
            closeDrawerBtn: document.querySelector('.close-drawer-btn'),
            form: document.getElementById('supplierForm'),
            tableBody: document.getElementById('suppliersTableBody'),
            drawerTitle: document.getElementById('drawerTitle'),
            searchInput: document.getElementById('searchInput'),
            statusFormGroup: document.getElementById('statusFormGroup'),
        },
        state: { suppliers: [] },

        init() {
            this.bindEvents();
            this.loadSuppliers();
        },

        bindEvents() {
            this.elements.addBtn.addEventListener('click', () => this.openDrawer());
            this.elements.closeDrawerBtn.addEventListener('click', () => this.closeDrawer());
            this.elements.form.addEventListener('submit', e => this.handleFormSubmit(e));
            this.elements.searchInput.addEventListener('input', e => this.renderTable(e.target.value));
            this.elements.tableBody.addEventListener('click', e => {
                const editBtn = e.target.closest('.edit-btn');
                if (editBtn) this.openDrawer(editBtn.dataset.id);

                // Use a specific class for the checkbox input to avoid ambiguity
                const statusToggle = e.target.closest('.status-toggle');
                if (statusToggle) {
                    this.handleStatusToggle(statusToggle.dataset.id, statusToggle.checked);
                }
            });
        },

        async loadSuppliers() {
            try {
                const response = await fetch('ajax/ajax_handler_suppliers.php?action=fetchAll');
                const data = await response.json();
                if (!data.success) throw new Error(data.message);
                this.state.suppliers = data.data;
                this.renderTable();
            } catch (error) {
                showToast('Failed to load suppliers: ' + error.message, 'error');
            }
        },

        renderTable(searchTerm = '') {
            const tbody = this.elements.tableBody;
            tbody.innerHTML = '';
            const filtered = this.state.suppliers.filter(s => 
                s.SupplierName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (s.ContactPerson && s.ContactPerson.toLowerCase().includes(searchTerm.toLowerCase()))
            );

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No suppliers found.</td></tr>';
                return;
            }

            filtered.forEach(s => {
                const isChecked = s.IsActive == 1 ? 'checked' : '';
                const statusToggle = `
                    <label class="switch">
                        <input type="checkbox" class="status-toggle" data-id="${s.SupplierID}" ${isChecked}>
                        <span class="slider"></span>
                    </label>
                `;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${this.escapeHTML(s.SupplierName)}</td>
                    <td>${this.escapeHTML(s.ContactPerson)}</td>
                    <td>${this.escapeHTML(s.PhoneNumber)}</td>
                    <td>${statusToggle}</td>
                    <td class="action-btns">
                        <button class="edit-btn" data-id="${s.SupplierID}" title="Edit"><span class="material-icons-outlined">edit</span></button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        },

        openDrawer(id = null) {
            this.elements.form.reset();
            document.getElementById('supplierId').value = '';
            
            if (id) {
                this.elements.drawerTitle.textContent = 'Edit Supplier';
                // **FIX:** Hide the status checkbox when editing, as it's handled by the toggle.
                this.elements.statusFormGroup.style.display = 'none';
                const supplier = this.state.suppliers.find(s => s.SupplierID == id);
                if (supplier) {
                    document.getElementById('supplierId').value = supplier.SupplierID;
                    document.getElementById('supplierName').value = supplier.SupplierName;
                    document.getElementById('contactPerson').value = supplier.ContactPerson;
                    document.getElementById('phoneNumber').value = supplier.PhoneNumber;
                }
            } else {
                this.elements.drawerTitle.textContent = 'Add New Supplier';
                // **FIX:** Ensure the status checkbox is visible when adding a new supplier.
                this.elements.statusFormGroup.style.display = 'block';
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
            
            // **FIX:** Only include 'is_active' for new suppliers.
            if (formData.get('supplier_id')) {
                formData.delete('is_active');
            } else {
                formData.set('is_active', document.getElementById('isActive').checked ? '1' : '0');
            }

            try {
                const response = await fetch('ajax/ajax_handler_suppliers.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (!data.success) throw new Error(data.message);
                
                this.closeDrawer();
                await this.loadSuppliers();
                showToast(data.message, 'success');
            } catch (error) {
                showToast('Error saving supplier: ' + error.message, 'error');
            }
        },

        async handleStatusToggle(id, isActive) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggleStatus');
                formData.append('supplier_id', id);
                formData.append('is_active', isActive ? '1' : '0');

                const response = await fetch('ajax/ajax_handler_suppliers.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (!data.success) throw new Error(data.message);
                
                const supplier = this.state.suppliers.find(s => s.SupplierID == id);
                if(supplier) supplier.IsActive = isActive ? 1 : 0;

                showToast(data.message, 'success');
            } catch (error) {
                showToast('Error updating status: ' + error.message, 'error');
                this.loadSuppliers(); // Re-load to show the correct state from the server
            }
        },

        escapeHTML(str) {
            if (str === null || str === undefined) return '';
            return str.toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
        }
    };
    
    App.init();
});
</script>

<?php require_once 'includes/footer.php'; ?>