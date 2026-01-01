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
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading transactions...</p>
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
            <div class="flex flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-4xl text-text-secondary mb-2">inbox</span>
                <p class="text-text-secondary">No transactions found</p>
            </div>
        `;
        return;
    }

    // Build HTML table
    let html = `
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background-light border-b border-surface-border">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Date</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Item</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Recipe</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Type</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap text-right">Quantity</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap text-right">Bal. Before</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap text-right">Bal. After</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border">
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

        const typeBadgeClass = transaction.type === 'recipe_usage' 
            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200'
            : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200';
        
        html += `
            <tr class="hover:bg-background-light/20 transition-colors group">
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap font-mono">${dateStr}</td>
                <td class="px-6 py-4 text-sm font-medium text-text-primary whitespace-nowrap">${escapeHtml(itemName)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${recipeName ? escapeHtml(recipeName) : '<span class="italic text-text-secondary/50">N/A</span>'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeBadgeClass}">
                        ${escapeHtml(typeLabel)}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium ${transaction.operation === '+' ? 'text-primary' : 'text-rose-600 dark:text-rose-400'} text-right whitespace-nowrap font-mono">${transaction.operation}${parseFloat(transaction.qty_used).toFixed(3)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary text-right whitespace-nowrap font-mono">${parseFloat(transaction.balance_before).toFixed(3)}</td>
                <td class="px-6 py-4 text-sm font-bold text-text-primary text-right whitespace-nowrap font-mono">${parseFloat(transaction.balance_after).toFixed(3)}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
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

