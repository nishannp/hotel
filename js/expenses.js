document.addEventListener('DOMContentLoaded', function() {

    // --- CONFIG & STATE ---
    const API_URL = 'ajax/ajax_handler_expenses.php';
    let state = {
        categories: [],
        businessUnits: [],
    };

    // --- UI SELECTORS ---
    const ui = {
        forms: {
            addExpense: document.getElementById('form-add-expense'),
            addCategory: document.getElementById('form-add-category'),
            editCategory: document.getElementById('form-edit-category'),
        },
        inputs: {
            expenseBusinessUnit: document.getElementById('expense-business-unit'),
            expenseCategory: document.getElementById('expense-category'),
            categoryName: document.getElementById('category-name'),
            filterMonth: document.getElementById('filter-month'),
            editCategoryId: document.getElementById('edit-category-id'),
            editCategoryName: document.getElementById('edit-category-name'),
        },
        containers: {
            categoryList: document.getElementById('category-list'),
            expensesTable: document.getElementById('expenses-table-container'),
        },
        buttons: {
            refreshExpenses: document.getElementById('btn-refresh-expenses'),
        },
        modals: {
            editCategory: document.getElementById('modal-edit-category'),
            closeModal: document.querySelector('#modal-edit-category .close-button'),
        },
        toast: document.getElementById('toast'),
    };

    // --- API & DATA HANDLING ---
    async function apiCall(action, options = {}) {
        const { method = 'GET', body = null } = options;
        const url = new URL(API_URL, window.location.href);
        url.searchParams.append('action', action);
        
        const fetchOptions = { method };
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
            const response = await apiCall('getInitialData');
            state.businessUnits = response.data.business_units;
            state.categories = response.data.categories;
            
            renderBusinessUnitDropdown();
            renderCategoryDropdown();
            renderCategoryList();
            
            await loadExpenses();
        } catch (error) { /* Handled by apiCall */ }
    }

    async function loadExpenses() {
        try {
            const month = ui.inputs.filterMonth.value;
            const response = await apiCall('getExpenses', { body: { month } });
            renderExpensesTable(response.data);
        } catch (error) { /* Handled */ }
    }

    // --- RENDER FUNCTIONS ---
    function renderBusinessUnitDropdown() {
        const select = ui.inputs.expenseBusinessUnit;
        select.innerHTML = '<option></option>'; // Placeholder for Select2
        state.businessUnits.forEach(unit => {
            const option = document.createElement('option');
            option.value = unit.id;
            option.textContent = unit.name;
            select.appendChild(option);
        });
    }

    function renderCategoryDropdown() {
        const select = ui.inputs.expenseCategory;
        select.innerHTML = '<option></option>'; // Placeholder for Select2
        state.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
        // Refresh the Select2 instance if it's already initialized
        if ($(select).data('select2')) {
            $(select).select2('destroy').select2({
                width: '100%',
                placeholder: 'Search and select a category'
            });
        }
    }

    function renderCategoryList() {
        const list = ui.containers.categoryList;
        list.innerHTML = '';
        if (state.categories.length === 0) {
            list.innerHTML = '<p style="padding: 1rem; text-align: center;">No categories found.</p>';
            return;
        }
        state.categories.forEach(cat => {
            const item = document.createElement('div');
            item.className = 'category-item';
            item.dataset.id = cat.id;
            item.innerHTML = `
                <span>${cat.name}</span>
                <div class="category-item-actions">
                    <button class="edit-btn" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="delete-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </div>
            `;
            list.appendChild(item);
        });
    }

    function renderExpensesTable(expenses) {
        const container = ui.containers.expensesTable;
        if (expenses.length === 0) {
            container.innerHTML = '<p style="padding: 2rem; text-align: center;">No expenses found for the selected period.</p>';
            return;
        }

        const table = document.createElement('table');
        table.className = 'expenses-table';
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Unit</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="amount-col">Amount</th>
                </tr>
            </thead>
            <tbody>
                ${expenses.map(exp => `
                    <tr>
                        <td>${exp.expense_date}</td>
                        <td>${exp.business_unit}</td>
                        <td>${exp.category}</td>
                        <td>${exp.description} ${exp.quantity ? `(${exp.quantity} items)` : ''}</td>
                        <td class="amount-col">Rs ${parseFloat(exp.amount).toFixed(2)}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;
        container.innerHTML = '';
        container.appendChild(table);
    }

    // --- EVENT HANDLERS ---
    function setupEventListeners() {
        ui.forms.addExpense.addEventListener('submit', handleAddExpense);
        ui.forms.addCategory.addEventListener('submit', handleAddCategory);
        ui.forms.editCategory.addEventListener('submit', handleUpdateCategory);
        
        ui.buttons.refreshExpenses.addEventListener('click', loadExpenses);
        ui.inputs.filterMonth.addEventListener('change', loadExpenses);

        ui.containers.categoryList.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-btn');
            if (editBtn) {
                const item = editBtn.closest('.category-item');
                handleEditCategoryClick(item.dataset.id);
            }
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                const item = deleteBtn.closest('.category-item');
                handleDeleteCategory(item.dataset.id);
            }
        });

        ui.modals.closeModal.addEventListener('click', () => ui.modals.editCategory.style.display = 'none');
    }

    async function handleAddExpense(e) {
        e.preventDefault();
        const formData = new FormData(ui.forms.addExpense);
        try {
            const response = await apiCall('addExpense', { method: 'POST', body: formData });
            showToast(response.message);
            ui.forms.addExpense.reset();
            // Reset Select2 fields
            $(ui.inputs.expenseBusinessUnit).val(null).trigger('change');
            $(ui.inputs.expenseCategory).val(null).trigger('change');
            await loadExpenses();
        } catch (error) { /* Handled */ }
    }

    async function handleAddCategory(e) {
        e.preventDefault();
        const formData = new FormData(ui.forms.addCategory);
        const name = formData.get('name');
        try {
            const response = await apiCall('addCategory', { method: 'POST', body: formData });
            showToast(response.message);
            ui.forms.addCategory.reset();
            // Add to state and re-render
            state.categories.push({ id: response.id, name: name });
            state.categories.sort((a, b) => a.name.localeCompare(b.name));
            renderCategoryDropdown();
            renderCategoryList();
        } catch (error) { /* Handled */ }
    }

    function handleEditCategoryClick(id) {
        const category = state.categories.find(c => c.id == id);
        if (category) {
            ui.inputs.editCategoryId.value = category.id;
            ui.inputs.editCategoryName.value = category.name;
            ui.modals.editCategory.style.display = 'flex';
        }
    }

    async function handleUpdateCategory(e) {
        e.preventDefault();
        const formData = new FormData(ui.forms.editCategory);
        try {
            const response = await apiCall('updateCategory', { method: 'POST', body: formData });
            showToast(response.message);
            ui.modals.editCategory.style.display = 'none';
            // Update state and re-render
            const id = parseInt(formData.get('id'));
            const name = formData.get('name');
            const index = state.categories.findIndex(c => c.id === id);
            if (index !== -1) {
                state.categories[index].name = name;
            }
            state.categories.sort((a, b) => a.name.localeCompare(b.name));
            renderCategoryDropdown();
            renderCategoryList();
        } catch (error) { /* Handled */ }
    }

    async function handleDeleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category? This cannot be undone.')) return;
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            const response = await apiCall('deleteCategory', { method: 'POST', body: formData });
            showToast(response.message);
            // Remove from state and re-render
            state.categories = state.categories.filter(c => c.id != id);
            renderCategoryDropdown();
            renderCategoryList();
        } catch (error) { /* Handled */ }
    }

    // --- HELPERS ---
    function showToast(message, isError = false) {
        ui.toast.textContent = message;
        ui.toast.className = `show ${isError ? 'error' : 'success'}`;
        setTimeout(() => { ui.toast.className = ''; }, 3000);
    }

    function setDefaultMonthFilter() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        ui.inputs.filterMonth.value = `${yyyy}-${mm}`;
    }

    function initializePlugins() {
        $(ui.inputs.expenseBusinessUnit).select2({
            width: '100%',
            placeholder: 'Select a business unit'
        });
        $(ui.inputs.expenseCategory).select2({
            width: '100%',
            placeholder: 'Search and select a category'
        });
    }

    // --- INITIALIZATION ---
    function init() {
        setDefaultMonthFilter();
        setupEventListeners();
        loadInitialData().then(() => {
            initializePlugins();
        });
    }

    init();
});
