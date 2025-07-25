<?php 
// ingredients.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Ingredient & Inventory';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Ingredients</h3>
        <button id="addBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Ingredient</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Name</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Quantity In Stock</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Unit</th>
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
            <h2 id="modalTitle">Add Ingredient</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="ingredientForm">
                <input type="hidden" id="ingredientId" name="ingredient_id">
                <div>
                    <label for="name">Ingredient Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="quantityInStock">Quantity In Stock:</label>
                    <input type="number" step="0.001" id="quantityInStock" name="quantity_in_stock" required>
                </div>
                <div>
                    <label for="unitOfMeasure">Unit of Measure (e.g., kg, liter, piece):</label>
                    <input type="text" id="unitOfMeasure" name="unit_of_measure" required>
                </div>
                <div>
                    <label for="supplierInfo">Supplier Info (Optional):</label>
                    <textarea id="supplierInfo" name="supplier_info" rows="3"></textarea>
                </div>
                <div>
                    <label for="imageUrl">Image URL (Optional):</label>
                    <input type="text" id="imageUrl" name="image_url">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = 'ajax/ajax_handler_ingredients.php';
    const formModal = document.getElementById('formModal');
    const mainForm = document.getElementById('ingredientForm');
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
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Name)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${parseFloat(item.QuantityInStock).toFixed(3)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.UnitOfMeasure)}</td>
                                <td style="padding: 12px; border: 1px solid #ddd;">
                                    <button class="edit-btn" data-id="${item.IngredientID}">Edit</button>
                                    <button class="delete-btn" data-id="${item.IngredientID}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No ingredients found.</td></tr>';
                }
            });
    }

    document.getElementById('addBtn').addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Add New Ingredient';
        mainForm.reset();
        document.getElementById('ingredientId').value = '';
        showModal();
    });

    formModal.querySelector('.close-button').addEventListener('click', hideModal);

    mainForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(mainForm);
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
                    document.getElementById('modalTitle').textContent = 'Edit Ingredient';
                    document.getElementById('ingredientId').value = item.IngredientID;
                    document.getElementById('name').value = item.Name;
                    document.getElementById('quantityInStock').value = item.QuantityInStock;
                    document.getElementById('unitOfMeasure').value = item.UnitOfMeasure;
                    document.getElementById('supplierInfo').value = item.SupplierInfo;
                    document.getElementById('imageUrl').value = item.ImageUrl;
                    showModal();
                }
            });
        }
        if (e.target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this ingredient? This action cannot be undone.')) {
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