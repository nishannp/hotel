<?php 
// menu_categories.php
require_once 'includes/header.php'; 
// Override the header title
echo "<script>document.querySelector('.content-header h1').textContent = 'Menu Categories';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Categories</h3>
        <button id="addCategoryBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Category</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Category Name</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Description</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody id="categoriesTableBody">
                <!-- Category rows will be inserted here by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Category</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <input type="hidden" id="categoryId" name="category_id">
                <div>
                    <label for="categoryName">Category Name:</label>
                    <input type="text" id="categoryName" name="category_name" required>
                </div>
                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const modal = document.getElementById('categoryModal');
    const closeModalBtn = document.querySelector('.close-button');
    const categoryForm = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('modalTitle');
    const tableBody = document.getElementById('categoriesTableBody');

    // --- Load all categories on page load ---
    function loadCategories() {
        fetch('ajax/ajax_handler_categories.php?action=fetchAll')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tableBody.innerHTML = ''; // Clear existing table
                    if (data.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 20px;">No categories found.</td></tr>';
                    } else {
                        data.data.forEach(cat => {
                            tableBody.innerHTML += `
                                <tr data-id="${cat.CategoryID}">
                                    <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(cat.CategoryName)}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(cat.Description)}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <button class="edit-btn" data-id="${cat.CategoryID}">Edit</button>
                                        <button class="delete-btn" data-id="${cat.CategoryID}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                }
            });
    }
    
    // --- Show/Hide Modal ---
    function showModal() { modal.style.display = 'block'; }
    function hideModal() { modal.style.display = 'none'; }

    addCategoryBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Add New Category';
        categoryForm.reset();
        document.getElementById('categoryId').value = '';
        showModal();
    });
    closeModalBtn.addEventListener('click', hideModal);
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            hideModal();
        }
    });

    // --- Handle Form Submission (Add & Update) ---
    categoryForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(categoryForm);
        const categoryId = document.getElementById('categoryId').value;
        const action = categoryId ? 'update' : 'add';
        formData.append('action', action);

        fetch('ajax/ajax_handler_categories.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideModal();
                loadCategories(); // Refresh the table
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        });
    });

    // --- Handle Edit and Delete button clicks (Event Delegation) ---
    tableBody.addEventListener('click', function(event) {
        const target = event.target;
        const id = target.dataset.id;

        // EDIT button clicked
        if (target.classList.contains('edit-btn')) {
            fetch('ajax/ajax_handler_categories.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=fetchSingle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalTitle.textContent = 'Edit Category';
                    document.getElementById('categoryId').value = data.data.CategoryID;
                    document.getElementById('categoryName').value = data.data.CategoryName;
                    document.getElementById('description').value = data.data.Description;
                    showModal();
                }
            });
        }

        // DELETE button clicked
        if (target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this category? This might affect existing menu items.')) {
                fetch('ajax/ajax_handler_categories.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCategories();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    });

      function escapeHTML(str) {
        return str.replace(/[&<>"']/g, function(match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[match];
        });
    }
    // Initial load
    loadCategories();
});
</script>

<?php require_once 'includes/footer.php'; ?>