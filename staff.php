<?php 
// staff.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Staff Management';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Staff Members</h3>
        <button id="addBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Staff</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Name</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Role</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Phone Number</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Hire Date</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Active</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="formModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Staff Member</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="staffForm">
                <input type="hidden" id="staffId" name="staff_id">
                <div>
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>
                 <div>
                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" name="last_name" required>
                </div>
                <div>
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Waiter">Waiter</option>
                        <option value="Chef">Chef</option>
                        <option value="Manager">Manager</option>
                        <option value="Cashier">Cashier</option>
                    </select>
                </div>
                <div>
                    <label for="phoneNumber">Phone Number:</label>
                    <input type="tel" id="phoneNumber" name="phone_number" required>
                </div>
                 <div>
                    <label for="hireDate">Hire Date:</label>
                    <input type="date" id="hireDate" name="hire_date" required>
                </div>
                <div>
                    <label><input type="checkbox" id="isActive" name="is_active" checked> Is Active</label>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// This JavaScript is very similar to menu_categories.php
// It can be adapted easily for Staff, Tables, and Ingredients
document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = 'ajax/ajax_handler_staff.php';
    const formModal = document.getElementById('formModal');
    const staffForm = document.getElementById('staffForm');
    const tableBody = document.getElementById('tableBody');

    // --- Generic Functions ---
    const showModal = () => formModal.style.display = 'block';
    const hideModal = () => formModal.style.display = 'none';
        const escapeHTML = str => str.toString().replace(/[&<>"']/g, match => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[match]));
    // --- Load Data ---
    function loadTableData() {
        fetch(`${ajaxUrl}?action=fetchAll`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                if(data.success && data.data.length > 0) {
                    data.data.forEach(item => {
                        tableBody.innerHTML += `
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.FirstName)} ${escapeHTML(item.LastName)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Role)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.PhoneNumber)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${item.HireDate}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${item.IsActive == 1 ? 'Yes' : 'No'}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <button class="edit-btn" data-id="${item.StaffID}">Edit</button>
                                    <button class="delete-btn" data-id="${item.StaffID}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">No staff members found.</td></tr>';
                }
            });
    }

    // --- Event Listeners ---
    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Add New Staff Member';
        staffForm.reset();
        document.getElementById('staffId').value = '';
        document.getElementById('isActive').checked = true;
        showModal();
    });

    formModal.querySelector('.close-button').addEventListener('click', hideModal);

    staffForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(staffForm);
        formData.append('action', 'save');
        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    hideModal();
                    loadTableData();
                }
            });
    });

    tableBody.addEventListener('click', function(e) {
        const id = e.target.dataset.id;
        if (e.target.classList.contains('edit-btn')) {
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=fetchSingle&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = data.data;
                    document.getElementById('modalTitle').textContent = 'Edit Staff Member';
                    document.getElementById('staffId').value = item.StaffID;
                    document.getElementById('firstName').value = item.FirstName;
                    document.getElementById('lastName').value = item.LastName;
                    document.getElementById('role').value = item.Role;
                    document.getElementById('phoneNumber').value = item.PhoneNumber;
                    document.getElementById('hireDate').value = item.HireDate;
                    document.getElementById('isActive').checked = (item.IsActive == 1);
                    showModal();
                }
            });
        }
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this staff member?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch(ajaxUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) loadTableData();
                });
            }
        }
    });

    loadTableData(); // Initial load
});
</script>

<?php require_once 'includes/footer.php'; ?>