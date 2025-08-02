document.addEventListener('DOMContentLoaded', function() {

    // --- STATE ---
    let state = {
        currentAdminId: null,
    };

    // --- UI SELECTORS ---
    const ui = {
        listContainer: document.getElementById('admins-list-container'),
        toast: document.getElementById('toast'),
        modals: {
            add: document.getElementById('modal-add-admin'),
            changePassword: document.getElementById('modal-change-password'),
        },
        forms: {
            add: document.getElementById('form-add-admin'),
            changePassword: document.getElementById('form-change-password'),
        },
        buttons: {
            showAddModal: document.getElementById('btn-show-add-modal'),
        }
    };

    const API_URL = 'ajax/ajax_handler_admins.php';

    // --- API CALLS ---
    async function apiCall(action, options = {}) {
        const { method = 'POST', body = null } = options;
        const url = new URL(API_URL, window.location.href);
        
        const fetchOptions = { method };

        if (method === 'POST') {
            const formData = body instanceof FormData ? body : new FormData();
            formData.append('action', action);
            fetchOptions.body = formData;
        } else { // For GET requests
            url.searchParams.append('action', action);
            if (body) {
                for (const [key, value] of Object.entries(body)) {
                    url.searchParams.append(key, value);
                }
            }
        }

        try {
            const response = await fetch(url.toString(), fetchOptions);
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'An unknown error occurred.');
            }
            return data;
        } catch (error) {
            showToast(error.message, true);
            throw error;
        }
    }

    // --- RENDER FUNCTIONS ---
    function renderAdmins(admins) {
        ui.listContainer.innerHTML = '';
        if (!admins || admins.length === 0) {
            ui.listContainer.innerHTML = '<p>No administrators found.</p>';
            return;
        }

        admins.forEach(admin => {
            const adminEl = document.createElement('div');
            adminEl.className = 'admin-item';
            adminEl.dataset.id = admin.AdminID;
            adminEl.dataset.username = admin.Username;

            const avatarLetter = admin.Username.charAt(0).toUpperCase();
            const createdDate = new Date(admin.CreatedAt).toLocaleDateString();
            const isCurrentUser = parseInt(admin.AdminID, 10) === state.currentAdminId;

            adminEl.innerHTML = `
                <div class="admin-avatar">${avatarLetter}</div>
                <div class="admin-details">
                    <div class="username">${admin.Username} ${isCurrentUser ? '(You)' : ''}</div>
                    <div class="created-date">Member since: ${createdDate}</div>
                </div>
                <div class="admin-phone">${admin.PhoneNumber || 'No phone'}</div>
                <div class="admin-actions">
                    <button class="btn btn-secondary btn-change-password">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button class="btn btn-danger btn-delete" ${isCurrentUser ? 'disabled' : ''}>
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            `;
            ui.listContainer.appendChild(adminEl);
        });
    }

    // --- EVENT HANDLERS ---
    async function handleAddAdmin(e) {
        e.preventDefault();
        const form = ui.forms.add;
        const password = form.querySelector('#add-password').value;
        const passwordConfirm = form.querySelector('#add-password-confirm').value;

        if (password !== passwordConfirm) {
            showToast('Passwords do not match.', true);
            return;
        }

        try {
            const data = await apiCall('addAdmin', { body: new FormData(form) });
            showToast(data.message);
            form.reset();
            closeModal(ui.modals.add);
            loadAdmins();
        } catch (error) { /* Handled by apiCall */ }
    }

    async function handleChangePassword(e) {
        e.preventDefault();
        const form = ui.forms.changePassword;
        const newPassword = form.querySelector('#new-password').value;
        const newPasswordConfirm = form.querySelector('#new-password-confirm').value;

        if (newPassword !== newPasswordConfirm) {
            showToast('New passwords do not match.', true);
            return;
        }

        try {
            const data = await apiCall('updatePassword', { body: new FormData(form) });
            showToast(data.message);
            form.reset();
            closeModal(ui.modals.changePassword);
        } catch (error) { /* Handled by apiCall */ }
    }

    function handleListClick(e) {
        const adminItem = e.target.closest('.admin-item');
        if (!adminItem) return;

        const adminId = adminItem.dataset.id;
        const username = adminItem.dataset.username;

        if (e.target.closest('.btn-delete')) {
            if (confirm(`Are you sure you want to delete the admin "${username}"?`)) {
                const formData = new FormData();
                formData.append('id', adminId);
                apiCall('deleteAdmin', { body: formData })
                    .then(data => {
                        showToast(data.message);
                        loadAdmins();
                    })
                    .catch(() => {}); // Errors handled by apiCall
            }
        } else if (e.target.closest('.btn-change-password')) {
            openChangePasswordModal(adminId, username);
        }
    }

    // --- MODAL & HELPERS ---
    function openModal(modal) { modal.style.display = 'flex'; }
    function closeModal(modal) { modal.style.display = 'none'; }

    function openChangePasswordModal(adminId, username) {
        const form = ui.forms.changePassword;
        form.reset();
        
        const currentPasswordGroup = form.querySelector('#current-password-group');
        const currentPasswordInput = form.querySelector('#current-password');
        const introText = form.querySelector('#change-password-intro');
        const usernameDisplay = form.querySelector('#change-password-username');
        const title = ui.modals.changePassword.querySelector('h2');

        form.querySelector('#change-password-admin-id').value = adminId;

        if (parseInt(adminId, 10) === state.currentAdminId) {
            title.innerHTML = '<i class="fas fa-key"></i> Change My Password';
            introText.style.display = 'none';
            currentPasswordGroup.style.display = 'block';
            currentPasswordInput.required = true;
        } else {
            title.innerHTML = '<i class="fas fa-key"></i> Change Password';
            introText.style.display = 'block';
            usernameDisplay.textContent = username;
            currentPasswordGroup.style.display = 'none';
            currentPasswordInput.required = false;
        }
        
        openModal(ui.modals.changePassword);
    }

    function showToast(message, isError = false) {
        ui.toast.textContent = message;
        ui.toast.className = `show ${isError ? 'error' : 'success'}`;
        setTimeout(() => { ui.toast.className = ''; }, 3500);
    }

    // --- INITIALIZATION ---
    async function loadAdmins() {
        try {
            const data = await apiCall('getAdmins', { method: 'GET' });
            state.currentAdminId = parseInt(data.current_admin_id, 10);
            renderAdmins(data.data);
        } catch (error) {
            ui.listContainer.innerHTML = `<p class="error-message">${error.message}</p>`;
        }
    }

    function setupEventListeners() {
        ui.forms.add.addEventListener('submit', handleAddAdmin);
        ui.forms.changePassword.addEventListener('submit', handleChangePassword);
        ui.listContainer.addEventListener('click', handleListClick);
        
        ui.buttons.showAddModal.addEventListener('click', () => openModal(ui.modals.add));
        
        document.querySelectorAll('.modal .close-button').forEach(btn => {
            btn.addEventListener('click', () => closeModal(btn.closest('.modal')));
        });
    }

    function init() {
        setupEventListeners();
        loadAdmins();
    }

    init();
});
