<?php 
// tables.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Restaurant Tables';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Tables</h3>
        <button id="addBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Table</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Table Number</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Capacity</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Status</th>
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
            <h2 id="modalTitle">Add Table</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="tableForm">
                <input type="hidden" id="tableId" name="table_id">
                <div>
                    <label for="tableNumber">Table Number:</label>
                    <input type="number" id="tableNumber" name="table_number" required>
                </div>
                 <div>
                    <label for="capacity">Capacity (seats):</label>
                    <input type="number" id="capacity" name="capacity" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// This JavaScript is almost identical to the staff management script
document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = 'ajax/ajax_handler_tables.php';
    const formModal = document.getElementById('formModal');
    const tableForm = document.getElementById('tableForm');
    const tableBody = document.getElementById('tableBody');

    const showModal = () => formModal.style.display = 'block';
    const hideModal = () => formModal.style.display = 'none';
       const escapeHTML = str => str.toString().replace(/[&<>"']/g, match => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[match]));
    function loadTableData() {
        fetch(`${ajaxUrl}?action=fetchAll`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';
                if(data.success && data.data.length > 0) {
                    data.data.forEach(item => {
                        tableBody.innerHTML += `
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.TableNumber)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Capacity)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Status)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <button class="edit-btn" data-id="${item.TableID}">Edit</button>
                                    <button class="delete-btn" data-id="${item.TableID}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No tables found.</td></tr>';
                }
            });
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Add New Table';
        tableForm.reset();
        document.getElementById('tableId').value = '';
        showModal();
    });

    formModal.querySelector('.close-button').addEventListener('click', hideModal);

    tableForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(tableForm);
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
                    document.getElementById('modalTitle').textContent = 'Edit Table';
                    document.getElementById('tableId').value = item.TableID;
                    document.getElementById('tableNumber').value = item.TableNumber;
                    document.getElementById('capacity').value = item.Capacity;
                    showModal();
                }
            });
        }
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this table?')) {
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

    loadTableData();
});
</script>

<?php require_once 'includes/footer.php'; ?>