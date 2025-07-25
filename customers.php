<?php 
// customers.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Customer Management';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Customers</h3>
        <button id="addBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Customer</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Name</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Phone Number</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Email</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div id="formModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Customer</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="customerForm">
                <input type="hidden" id="customerId" name="customer_id">
                <div>
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" name="first_name" required>
                </div>
                 <div>
                    <label for="lastName">Last Name (Optional):</label>
                    <input type="text" id="lastName" name="last_name">
                </div>
                <div>
                    <label for="phoneNumber">Phone Number:</label>
                    <input type="tel" id="phoneNumber" name="phone_number" required>
                </div>
                 <div>
                    <label for="email">Email (Optional):</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Configuration & Element Selection ---
    const ajaxUrl = 'ajax/ajax_handler_customers.php';
    const formModal = document.getElementById('formModal');
    const customerForm = document.getElementById('customerForm');
    const tableBody = document.getElementById('tableBody');
    const addBtn = document.getElementById('addBtn');
    const closeModalBtn = formModal.querySelector('.close-button');
    const modalTitle = document.getElementById('modalTitle');

    // --- Utility Functions ---
    const showModal = () => formModal.style.display = 'block';
    const hideModal = () => formModal.style.display = 'none';
       const escapeHTML = str => str.toString().replace(/[&<>"']/g, match => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[match]));
    // --- Main Data Loading Function ---
    function loadTableData() {
        fetch(`${ajaxUrl}?action=fetchAll`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ''; // Clear previous data
                if (data.success && data.data.length > 0) {
                    data.data.forEach(item => {
                        tableBody.innerHTML += `
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.FirstName)} ${escapeHTML(item.LastName)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.PhoneNumber)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Email)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <button class="edit-btn" data-id="${item.CustomerID}">Edit</button>
                                    <button class="delete-btn" data-id="${item.CustomerID}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No customers found. Click "Add New Customer" to start.</td></tr>';
                }
            });
    }

    // --- Event Listener for "Add New Customer" Button ---
    addBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Add New Customer';
        customerForm.reset();
        document.getElementById('customerId').value = ''; // Ensure ID is cleared
        showModal();
    });

    // --- Event Listener to Close Modal ---
    closeModalBtn.addEventListener('click', hideModal);
    window.addEventListener('click', (event) => {
        if (event.target == formModal) {
            hideModal();
        }
    });

    // --- Event Listener for Form Submission (Handles both Add and Edit) ---
    customerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(customerForm);
        formData.append('action', 'save');

        fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    hideModal();
                    loadTableData(); // Refresh the table with new data
                }
            });
    });

    // --- Event Delegation for Edit and Delete Buttons ---
    tableBody.addEventListener('click', function(e) {
        const id = e.target.dataset.id;
        if (!id) return; // Exit if the click wasn't on a button with a data-id

        // Handle EDIT button click
        if (e.target.classList.contains('edit-btn')) {
            const formData = new FormData();
            formData.append('action', 'fetchSingle');
            formData.append('id', id);

            fetch(ajaxUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const item = data.data;
                    modalTitle.textContent = 'Edit Customer';
                    document.getElementById('customerId').value = item.CustomerID;
                    document.getElementById('firstName').value = item.FirstName;
                    document.getElementById('lastName').value = item.LastName;
                    document.getElementById('phoneNumber').value = item.PhoneNumber;
                    document.getElementById('email').value = item.Email;
                    showModal();
                } else {
                    alert('Could not fetch customer details.');
                }
            });
        }

        // Handle DELETE button click
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this customer? This cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch(ajaxUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        loadTableData(); // Refresh the table
                    }
                });
            }
        }
    });

    // --- Initial Load ---
    loadTableData();
});
</script>

<?php require_once 'includes/footer.php'; ?>