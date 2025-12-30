/**
 * Items Management Page
 * This file handles all CRUD operations for items using AJAX
 */

// Current page state
let currentPage = 1;
let currentSearch = '';
let currentPerPage = 10;

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadItems();
});

/**
 * Load items from API
 * This function makes an AJAX GET request to fetch items
 * 
 * Note: In MVC mode, we send AJAX request to the same page (/items)
 * Controller detects it's an AJAX request and returns JSON instead of HTML
 */
async function loadItems() {
    try {
        // Show loading state
        document.getElementById('itemsTableContainer').innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading Items...</p>
            </div>
        `;

        // Prepare query parameters for pagination and search
        const params = {
            page: currentPage,
            per_page: currentPerPage
        };

        // Add search parameter if user has typed something
        if (currentSearch) {
            params.search = currentSearch;
        }

        // Make AJAX GET request
        // In MVC mode: use /items route (same page)
        // Controller detects AJAX header and returns JSON
        const response = await apiGet('/items', params);

        // Check if request was successful
        if (response.success) {
            // Display items in table
            displayItems(response.data);
            
            // Display pagination
            displayPagination(response.pagination);
        } else {
            showError('Failed to load the items');
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showError(error.message || 'Failed to load the items');
        
        // Show error message in table container
        document.getElementById('itemsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem; color: #e74c3c;">
                <p>Error: ${error.message}</p>
                <button class="btn btn-primary mt-1" onclick="loadItems()">Try Again</button>
            </div>
        `;
    }
}

/**
 * Display items in a table
 * @param {Array} items - Array of item objects
 */
function displayItems(items) {
    if (items.length === 0) {
        document.getElementById('itemsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem;">
                <p>No items found</p>
            </div>
        `;
        return;
    }

    // Build HTML table
    let html = `
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Short Name</th>
                        <th>Name</th>
                        <th>Balance</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Loop through items and create table rows
    items.forEach(item => {
        html += `
            <tr>
                <td>${item.id}</td>
                <td>${item.short_name}</td>
                <td>${item.name}</td>
                <td>${item.balance}</td>
                <td>${item.unit}</td>
                <td>
                    <button class="btn btn-primary btn-small" onclick="editItem(${item.id})">Edit</button>
                    <button class="btn btn-danger btn-small" onclick="deleteItem(${item.id})">Delete</button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('itemsTableContainer').innerHTML = html;
}

/**
 * Display pagination controls
 * @param {Object} pagination - Pagination data from API
 */
function displayPagination(pagination) {
    if (!pagination || pagination.last_page <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    let html = '';

    // Previous button
    html += `
        <button ${pagination.current_page === 1 ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page - 1})">
            Previous
        </button>
    `;

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || 
            (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `
                <button class="${i === pagination.current_page ? 'active' : ''}" 
                        onclick="goToPage(${i})">
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += `<span>...</span>`;
        }
    }

    // Next button
    html += `
        <button ${pagination.current_page === pagination.last_page ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page + 1})">
            Next
        </button>
    `;

    // Show page info
    html += `
        <span style="margin-left: 1rem; padding: 0.5rem;">
            Page ${pagination.current_page} of ${pagination.last_page} 
            (Total: ${pagination.total} items)
        </span>
    `;

    document.getElementById('pagination').innerHTML = html;
}

/**
 * Navigate to specific page
 * @param {number} page - Page number
 */
function goToPage(page) {
    currentPage = page;
    loadItems();
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Handle search input
 * This is called when user types in the search box
 */
function handleSearch() {
    const searchValue = document.getElementById('searchInput').value.trim();
    
    // Reset to page 1 when searching
    currentPage = 1;
    currentSearch = searchValue;
    
    // Load items with new search term
    loadItems();
}

/**
 * Open modal for creating new item
 */
function openItemModal() {
    // Reset form
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('modalTitle').textContent = 'Add New Item';
    
    // Show modal
    document.getElementById('itemModal').style.display = 'block';
}

/**
 * Close item modal
 */
function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
    document.getElementById('itemForm').reset();
}

/**
 * Edit item - Load item data and open modal
 * @param {number} id - Item ID
 */
async function editItem(id) {
    try {
        // Show loading in modal
        document.getElementById('modalTitle').textContent = 'Loading...';
        document.getElementById('itemModal').style.display = 'block';

        // Make AJAX GET request to fetch item details
        // In MVC mode: use API route directly
        const response = await apiGet(`/items/${id}`);

        if (response.success) {
            const item = response.data;

            // Fill form with item data
            document.getElementById('itemId').value = item.id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('itemShortName').value = item.short_name;
            document.getElementById('itemBalance').value = item.balance;
            document.getElementById('itemUnit').value = item.unit;
            document.getElementById('modalTitle').textContent = 'Edit Item';
        } else {
            showError('Failed to load item');
            closeItemModal();
        }
    } catch (error) {
        console.error('Error loading item:', error);
        showError(error.message || 'Failed to load item');
        closeItemModal();
    }
}

/**
 * Save item (Create or Update)
 * This function handles both creating new items and updating existing ones
 * @param {Event} event - Form submit event
 */
async function saveItem(event) {
    event.preventDefault(); // Prevent form from submitting normally

    try {
        // Get form data
        const itemId = document.getElementById('itemId').value;
        const itemData = {
            name: document.getElementById('itemName').value.trim(),
            short_name: document.getElementById('itemShortName').value.trim(),
            balance: parseFloat(document.getElementById('itemBalance').value),
            unit: document.getElementById('itemUnit').value.trim()
        };

        let response;

        if (itemId) {
            // Update existing item - Use PUT request
            response = await apiPut(`/items/${itemId}`, itemData);
        } else {
            // Create new item - Use POST request
            response = await apiPost('/items', itemData);
        }

        if (response.success) {
            showSuccess(response.message || (itemId ? 'Item updated successfully' : 'Item created successfully'));
            closeItemModal();
            loadItems(); // Reload items list
        } else {
            showError(response.message || 'Failed to save item');
        }
    } catch (error) {
        console.error('Error saving item:', error);
        
        // Check if it's a validation error
        if (error.message && error.message.includes('Validation')) {
            showError('Please check your input. All fields are required and must be valid.');
        } else {
            showError(error.message || 'Failed to save item');
        }
    }
}

/**
 * Delete item
 * @param {number} id - Item ID
 */
async function deleteItem(id) {
    // Confirm deletion
    const confirmed = await confirmAction('Are you sure you want to delete this item? This action cannot be undone.', 'Delete Item');
    if (!confirmed) {
        return;
    }

    try {
        // Make AJAX DELETE request
        const response = await apiDelete(`/items/${id}`);

        if (response.success) {
            showSuccess(response.message || 'Item deleted successfully');
            loadItems(); // Reload items list
        } else {
            showError(response.message || 'Failed to delete item');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showError(error.message || 'Failed to delete item');
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('itemModal');
    if (event.target === modal) {
        closeItemModal();
    }
}

