/**
 * Transactions History Page
 * This file handles displaying transaction history with pagination and search
 */

// Current page state
let currentPage = 1;
let currentSearch = '';
let currentPerPage = 10;
let searchTimeout = null;

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadTransactions();
});

/**
 * Load transactions from API
 */
async function loadTransactions() {
    try {
        // Show loading state
        document.getElementById('transactionsTableContainer').innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading Transactions...</p>
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
        const response = await apiGet('/transactions', params);

        // Check if request was successful
        if (response.success) {
            // Display transactions in table
            renderTransactionsTable(response.data);
            
            // Display pagination
            renderPagination(response.pagination);
        } else {
            showError('Failed to load the transactions');
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        showError(error.message || 'Failed to load the transactions');
        
        // Show error message in table container
        document.getElementById('transactionsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem; color: #e74c3c;">
                <p>Error: ${error.message}</p>
                <button class="btn btn-primary mt-1" onclick="loadTransactions()">Try Again</button>
            </div>
        `;
    }
}

/**
 * Display transactions in a table
 * @param {Array} transactions - Array of transaction objects
 */
function renderTransactionsTable(transactions) {
    if (!transactions || transactions.length === 0) {
        document.getElementById('transactionsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem;">
                <p>No transactions found</p>
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
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Item</th>
                        <th>Recipe</th>
                        <th>Type</th>
                        <th>Operation</th>
                        <th>Quantity</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Loop through transactions and create table rows
    transactions.forEach((transaction, index) => {
        const rowNumber = (currentPage - 1) * currentPerPage + index + 1;
        const date = new Date(transaction.created_at);
        const dateStr = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const itemName = transaction.item ? transaction.item.name : 'N/A';
        const itemShortName = transaction.item ? transaction.item.short_name : '';
        const recipeName = transaction.recipe ? transaction.recipe.name : '-';
        const typeLabel = getTypeLabel(transaction.type);
        const operationSymbol = transaction.operation === '+' ? '+' : '-';
        const operationColor = transaction.operation === '+' ? 'var(--success)' : 'var(--danger)';

        html += `
            <tr>
                <td>${rowNumber}</td>
                <td>${dateStr}</td>
                <td>
                    <div style="font-weight: 500;">${escapeHtml(itemName)}</div>
                    <small style="color: var(--text-secondary);">${escapeHtml(itemShortName)}</small>
                </td>
                <td>${recipeName ? escapeHtml(recipeName) : '-'}</td>
                <td>
                    <span class="transaction-type-badge" style="background: ${getTypeColor(transaction.type)};">
                        ${escapeHtml(typeLabel)}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span style="color: ${operationColor}; font-weight: 700; font-size: 1.2rem;">${operationSymbol}</span>
                </td>
                <td style="text-align: right; font-weight: 500;">${parseFloat(transaction.qty_used).toFixed(3)}</td>
                <td style="text-align: right; color: var(--text-secondary);">${parseFloat(transaction.balance_before).toFixed(3)}</td>
                <td style="text-align: right; font-weight: 600; color: var(--text-primary);">${parseFloat(transaction.balance_after).toFixed(3)}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('transactionsTableContainer').innerHTML = html;
}

/**
 * Get type label
 */
function getTypeLabel(type) {
    const labels = {
        'add_item': 'Item Update',
        'recipe_usage': 'Recipe Usage'
    };
    return labels[type] || type;
}

/**
 * Get type color
 */
function getTypeColor(type) {
    const colors = {
        'add_item': 'rgba(59, 130, 246, 0.1)',
        'recipe_usage': 'rgba(16, 185, 129, 0.1)'
    };
    return colors[type] || 'var(--bg-secondary)';
}

/**
 * Display pagination controls
 * @param {Object} pagination - Pagination data from API
 */
function renderPagination(pagination) {
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
            (Total: ${pagination.total} transactions)
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
    loadTransactions();
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Handle search input
 */
function handleSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchValue = searchInput.value.trim();

    // Clear existing timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Debounce search - wait 500ms after user stops typing
    searchTimeout = setTimeout(() => {
        currentSearch = searchValue;
        currentPage = 1; // Reset to first page when searching
        loadTransactions();
    }, 500);
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

