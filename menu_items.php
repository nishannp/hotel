<?php 
// menu_items.php
require_once 'includes/header.php'; 
echo "<script>document.querySelector('.content-header h1').textContent = 'Menu Items';</script>";
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>All Menu Items</h3>
        <button id="addItemBtn" class="btn-primary" style="padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Add New Item</button>
    </div>
    <div class="card-body">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Name</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Category</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Price</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Available</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                <!-- Item rows will be inserted here by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Menu Item</h2>
            <span class="close-button">×</span>
        </div>
        <div class="modal-body">
            <form id="itemForm">
                <input type="hidden" id="menuItemId" name="menu_item_id">
                <div>
                    <label for="itemName">Item Name:</label>
                    <input type="text" id="itemName" name="item_name" required>
                </div>
                <div>
                    <label for="itemCategory">Category:</label>
                    <select id="itemCategory" name="item_category" required>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
                <div>
                    <label for="itemPrice">Price:</label>
                    <input type="number" id="itemPrice" name="item_price" step="0.01" required>
                </div>
                 <div>
                    <label for="imageUrl">Image URL (Optional):</label>
                    <input type="text" id="imageUrl" name="image_url">
                </div>
                <div>
                    <label for="itemDescription">Description:</label>
                    <textarea id="itemDescription" name="item_description" rows="3"></textarea>
                </div>
                <div>
                    <label>
                        <input type="checkbox" id="isAvailable" name="is_available" checked> Is Available
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('addItemBtn');
    const modal = document.getElementById('itemModal');
    const closeModalBtn = modal.querySelector('.close-button');
    const itemForm = document.getElementById('itemForm');
    const modalTitle = document.getElementById('modalTitle');
    const tableBody = document.getElementById('itemsTableBody');
    const categorySelect = document.getElementById('itemCategory');

    // --- Utility function to populate category dropdown ---
    function populateCategories(selectedCategoryId = null) {
        fetch('ajax/ajax_handler_categories.php?action=fetchAll')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    categorySelect.innerHTML = '<option value="">-- Select a Category --</option>';
                    data.data.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.CategoryID;
                        option.textContent = cat.CategoryName;
                        if (cat.CategoryID == selectedCategoryId) {
                            option.selected = true;
                        }
                        categorySelect.appendChild(option);
                    });
                }
            });
    }

    // --- Load all menu items on page load ---
    function loadItems() {
        fetch('ajax/ajax_handler_menuitems.php?action=fetchAll')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tableBody.innerHTML = '';
                    if (data.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">No menu items found.</td></tr>';
                    } else {
                        data.data.forEach(item => {
                            tableBody.innerHTML += `
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.Name)}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">${escapeHTML(item.CategoryName || 'N/A')}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">$${parseFloat(item.Price).toFixed(2)}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">${item.IsAvailable == 1 ? 'Yes' : 'No'}</td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">
                                        <button class="edit-btn" data-id="${item.MenuItemID}">Edit</button>
                                        <button class="delete-btn" data-id="${item.MenuItemID}">Delete</button>
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

    addItemBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Add New Menu Item';
        itemForm.reset();
        document.getElementById('menuItemId').value = '';
        document.getElementById('isAvailable').checked = true;
        populateCategories(); // Populate for new item
        showModal();
    });
    closeModalBtn.addEventListener('click', hideModal);
    window.addEventListener('click', (event) => {
        if (event.target == modal) hideModal();
    });

    // --- Handle Form Submission (Add & Update) ---
    itemForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(itemForm);
        formData.append('action', 'save');

        fetch('ajax/ajax_handler_menuitems.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideModal();
                loadItems(); // Refresh the table
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

        if (target.classList.contains('edit-btn')) {
            fetch('ajax/ajax_handler_menuitems.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=fetchSingle&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = data.data;
                    modalTitle.textContent = 'Edit Menu Item';
                    document.getElementById('menuItemId').value = item.MenuItemID;
                    document.getElementById('itemName').value = item.Name;
                    document.getElementById('itemPrice').value = item.Price;
                    document.getElementById('itemDescription').value = item.Description;
                    document.getElementById('imageUrl').value = item.ImageUrl;
                    document.getElementById('isAvailable').checked = (item.IsAvailable == 1);
                    populateCategories(item.CategoryID); // Populate and pre-select category
                    showModal();
                }
            });
        }

        if (target.classList.contains('delete-btn')) {
            if (confirm('Are you sure you want to delete this menu item?')) {
                fetch('ajax/ajax_handler_menuitems.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadItems();
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    });
    
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>"']/g, match => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[match]));
    }
    
    // Initial load
    loadItems();
});
</script>

<?php require_once 'includes/footer.php'; ?>