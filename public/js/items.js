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
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading items...</p>
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
        
        // Add status filter if selected
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter && statusFilter.value !== 'all') {
            params.status = statusFilter.value;
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
            <div class="flex flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">inbox</span>
                <p class="text-text-secondary">No items found</p>
            </div>
        `;
        return;
    }

    // Build HTML table
    let html = `
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background-light border-b border-surface-border">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Item Details</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Code</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Balance</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Unit</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border">
    `;

    // Loop through items and create table rows
    items.forEach((item, index) => {
        const rowNumber = (currentPage - 1) * currentPerPage + index + 1;
        const balance = parseFloat(item.balance);
        const statusClass = balance === 0 
            ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
            : 'bg-primary/20 text-primary border border-primary/20';
        const statusText = balance === 0 ? 'Out of Stock' : 'In Stock';
        
        html += `
            <tr class="hover:bg-background-light/20 transition-colors group">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0 bg-surface-border rounded-lg flex items-center justify-center text-text-secondary">
                            <span class="material-symbols-outlined text-xl">inventory_2</span>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-text-primary">${escapeHtml(item.name)}</div>
                            <div class="text-xs text-text-secondary">${escapeHtml(item.short_name)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded bg-surface-border text-text-secondary font-mono">
                        ${escapeHtml(item.short_name)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-text-primary font-mono">${parseFloat(item.balance).toFixed(2)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">${escapeHtml(item.unit)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                        ${statusText}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="editItem(${item.id})" class="p-2 text-text-secondary hover:text-primary transition-colors rounded-full hover:bg-background-light" title="Edit">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button onclick="deleteItem(${item.id})" class="p-2 text-text-secondary hover:text-red-500 transition-colors rounded-full hover:bg-background-light" title="Delete">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
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

    // Show page info
    html += `
        <span class="text-sm text-text-secondary">
            Showing <span class="font-medium text-text-primary">${((pagination.current_page - 1) * pagination.per_page) + 1}</span> to 
            <span class="font-medium text-text-primary">${Math.min(pagination.current_page * pagination.per_page, pagination.total)}</span> of 
            <span class="font-medium text-text-primary">${pagination.total}</span> results
        </span>
    `;
    
    // Pagination buttons
    html += `<div class="flex items-center gap-1">`;
    
    // Previous button
    html += `
        <button ${pagination.current_page === 1 ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page - 1})"
                class="p-2 rounded-lg hover:bg-background-light disabled:opacity-50 text-text-secondary hover:text-text-primary transition-colors ${pagination.current_page === 1 ? 'cursor-not-allowed' : ''}">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>
    `;

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || 
            (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            const isActive = i === pagination.current_page;
            html += `
                <button onclick="goToPage(${i})"
                        class="w-8 h-8 flex items-center justify-center rounded-lg ${isActive ? 'bg-primary text-white shadow-[0_0_10px_rgba(45,212,191,0.15)]' : 'hover:bg-background-light text-text-secondary hover:text-text-primary'} text-sm font-medium transition-colors">
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += `<span class="px-2 text-text-secondary">...</span>`;
        }
    }

    // Next button
    html += `
        <button ${pagination.current_page === pagination.last_page ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page + 1})"
                class="p-2 rounded-lg hover:bg-background-light disabled:opacity-50 text-text-secondary hover:text-text-primary transition-colors ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed' : ''}">
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
    `;
    
    html += `</div>`;

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
 * Handle status filter
 */
function handleStatusFilter() {
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        // Store filter value and reload items
        currentPage = 1;
        loadItems();
    }
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
    document.getElementById('itemModal').classList.remove('hidden');
}

/**
 * Close item modal
 */
function closeItemModal() {
    document.getElementById('itemModal').classList.add('hidden');
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
        document.getElementById('itemModal').classList.remove('hidden');

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

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('itemModal');
    if (event.target === modal || event.target.closest('.bg-black\\/50')) {
        closeItemModal();
    }
}

