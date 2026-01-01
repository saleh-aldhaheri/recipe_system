/**
 * Dashboard JavaScript
 * Handles all dashboard functionality including widgets, charts, and tables
 */

let currentTimeFrame = 'last_week';
let charts = {};

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

/**
 * Convert time frame to dates (from, to)
 * @param {string} timeFrame - 'last_week', 'last_month', 'last_year'
 * @returns {object} {from: string, to: string} - Dates in Y-m-d format
 */
function getTimeFrameDates(timeFrame) {
    const now = new Date();
    const to = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const from = new Date(to);
    
    switch (timeFrame) {
        case 'last_week':
            from.setDate(from.getDate() - 7);
            break;
        case 'last_month':
            from.setMonth(from.getMonth() - 1);
            break;
        case 'last_year':
            from.setFullYear(from.getFullYear() - 1);
            break;
        default:
            from.setDate(from.getDate() - 7); // Default: last week
    }
    
    return {
        from: formatDateForAPI(from),
        to: formatDateForAPI(to)
    };
}

/**
 * Format date to Y-m-d format for API
 * @param {Date} date
 * @returns {string}
 */
function formatDateForAPI(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Load all dashboard data
 */
async function loadDashboardData() {
    currentTimeFrame = document.getElementById('timeFrameSelect').value;
    
    try {
        await Promise.all([
            loadStats(),
            loadTableData(),
            loadChartData('transactionsByType'),
            loadChartData('topItems'),
            loadChartData('dailyTrend'),
            loadChartData('usageByRecipes')
        ]);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        if (typeof notifications !== 'undefined') {
            notifications.error('Failed to load dashboard data');
        }
    }
}

/**
 * Load statistics widgets
 */
async function loadStats() {
    try {
        const dates = getTimeFrameDates(currentTimeFrame);
        const response = await apiPost('/dashboard/stats', dates);

        if (response.success && response.data) {
            const stats = response.data;
            
            // Update widgets
            document.getElementById('statTotalUsage').textContent = 
                formatNumber(stats.total_usage || 0);
            document.getElementById('statTransactions').textContent = 
                formatNumber(stats.transactions_count || 0);
            document.getElementById('statRecipes').textContent = 
                formatNumber(stats.recipes_count || 0);
            document.getElementById('statActiveItems').textContent = 
                formatNumber(stats.active_items_count || 0);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

/**
 * Load items summary table
 */
async function loadTableData() {
    const container = document.getElementById('itemsTableContainer');
    
    container.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div><p class="mt-4 text-text-secondary">Loading items...</p></div>';
    
    try {
        // Use global time frame selector
        const dates = getTimeFrameDates(currentTimeFrame);
        const response = await apiPost('/dashboard/items-summary', dates);

        if (response.success && response.data && response.data.items) {
            renderItemsTable(response.data.items);
        } else {
            container.innerHTML = '<p>No items found</p>';
        }
    } catch (error) {
        console.error('Error loading table data:', error);
        container.innerHTML = '<p class="text-danger">Error loading items</p>';
        if (typeof notifications !== 'undefined') {
            notifications.error('Failed to load items table');
        }
    }
}

/**
 * Render items table
 */
function renderItemsTable(items) {
    const container = document.getElementById('itemsTableContainer');
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><span class="material-symbols-outlined text-4xl text-text-secondary mb-2">inbox</span><p class="text-text-secondary">No items found</p></div>';
        return;
    }

    let html = `
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background-light border-b border-surface-border">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">#</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Name</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Short Name</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Total Usage</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Balance</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Unit</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border">
    `;

    items.forEach((item, index) => {
        html += `
            <tr class="hover:bg-surface-border/20 transition-colors group">
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-text-primary whitespace-nowrap">${escapeHtml(item.name)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${escapeHtml(item.short_name)}</td>
                <td class="px-6 py-4 text-sm text-text-primary whitespace-nowrap font-mono">${formatNumber(item.total)}</td>
                <td class="px-6 py-4 text-sm text-text-primary whitespace-nowrap font-mono">${formatNumber(item.balance)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${escapeHtml(item.unit)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button onclick="viewItemRecipes(${item.id}, '${escapeHtml(item.name)}')" class="px-3 py-1.5 rounded-lg bg-primary/20 text-primary hover:bg-primary hover:text-white text-xs font-medium transition-colors">
                        View Recipes
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

/**
 * Load chart data
 */
async function loadChartData(chartType) {
    // Use global time frame selector
    const dates = getTimeFrameDates(currentTimeFrame);
    
    try {
        let response;
        let data;
        
        switch (chartType) {
            case 'transactionsByType':
                response = await apiPost('/dashboard/transactions-by-type', dates);
                if (response.success && response.data) {
                    renderTransactionsByTypeChart(response.data);
                }
                break;
                
            case 'topItems':
                response = await apiPost('/dashboard/top-items', {
                    ...dates,
                    limit: 10
                });
                if (response.success && response.data && response.data.items) {
                    renderTopItemsChart(response.data.items);
                }
                break;
                
            case 'dailyTrend':
                response = await apiPost('/dashboard/daily-trend', dates);
                if (response.success && response.data && response.data.daily_data) {
                    renderDailyTrendChart(response.data.daily_data);
                }
                break;
                
            case 'usageByRecipes':
                response = await apiPost('/dashboard/usage-by-recipes', {
                    ...dates,
                    limit: 10
                });
                if (response.success && response.data && response.data.recipes) {
                    renderUsageByRecipesChart(response.data.recipes);
                }
                break;
        }
    } catch (error) {
        console.error(`Error loading ${chartType} chart:`, error);
    }
}

/**
 * Render Transactions by Type Pie Chart
 */
function renderTransactionsByTypeChart(data) {
    const ctx = document.getElementById('transactionsByTypeChart');
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (charts.transactionsByType) {
        charts.transactionsByType.destroy();
    }
    
    charts.transactionsByType = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Recipe Usage', 'Update Item'],
            datasets: [{
                data: [data.recipe_usage || 0, data.update_item || 0],
                backgroundColor: [
                    'rgba(45, 212, 191, 0.8)', // primary teal
                    'rgba(59, 130, 246, 0.8)'  // blue
                ],
                borderColor: [
                    'rgba(45, 212, 191, 1)',
                    'rgba(59, 130, 246, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8' // text-secondary
                    }
                },
                title: {
                    display: false
                }
            }
        }
    });
}

/**
 * Render Top Items Bar Chart
 */
function renderTopItemsChart(items) {
    const ctx = document.getElementById('topItemsChart');
    if (!ctx) return;
    
    if (charts.topItems) {
        charts.topItems.destroy();
    }
    
    const labels = items.map(item => item.short_name || item.item_name);
    const data = items.map(item => item.total_used);
    
    charts.topItems = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Used',
                data: data,
                backgroundColor: 'rgba(45, 212, 191, 0.8)', // primary teal
                borderColor: 'rgba(45, 212, 191, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

/**
 * Render Daily Trend Line Chart
 */
function renderDailyTrendChart(dailyData) {
    const ctx = document.getElementById('dailyTrendChart');
    if (!ctx) return;
    
    if (charts.dailyTrend) {
        charts.dailyTrend.destroy();
    }
    
    const labels = dailyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    const data = dailyData.map(item => item.total);
    
    charts.dailyTrend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Usage',
                data: data,
                borderColor: 'rgba(45, 212, 191, 1)', // primary teal
                backgroundColor: 'rgba(45, 212, 191, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

/**
 * Render Usage by Recipes Bar Chart
 */
function renderUsageByRecipesChart(recipes) {
    const ctx = document.getElementById('usageByRecipesChart');
    if (!ctx) return;
    
    if (charts.usageByRecipes) {
        charts.usageByRecipes.destroy();
    }
    
    const labels = recipes.map(recipe => recipe.recipe_name);
    const data = recipes.map(recipe => recipe.total_used);
    
    charts.usageByRecipes = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Used',
                data: data,
                backgroundColor: 'rgba(45, 212, 191, 0.8)', // primary teal
                borderColor: 'rgba(45, 212, 191, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8' // text-secondary
                    },
                    grid: {
                        color: 'rgba(51, 65, 85, 0.3)' // surface-border
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

/**
 * View recipes for a specific item
 */
async function viewItemRecipes(itemId, itemName) {
    const modal = document.getElementById('recipesModal');
    const modalTitle = document.getElementById('recipesModalTitle');
    const modalBody = document.getElementById('recipesModalBody');
    
    modalTitle.textContent = `Recipes for: ${itemName}`;
    modalBody.innerHTML = '<div class="flex flex-col items-center justify-center py-12"><div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div><p class="mt-4 text-text-secondary">Loading recipes...</p></div>';
    modal.classList.remove('hidden');
    
    try {
        const dates = getTimeFrameDates(currentTimeFrame);
        const response = await apiPost(`/dashboard/item/${itemId}/recipes`, dates);
        
        if (response.success && response.data && response.data.recipes) {
            renderRecipesList(response.data.recipes);
        } else {
            modalBody.innerHTML = '<p>No recipes found for this item</p>';
        }
    } catch (error) {
        console.error('Error loading item recipes:', error);
        modalBody.innerHTML = '<p class="text-danger">Error loading recipes</p>';
        if (typeof notifications !== 'undefined') {
            notifications.error('Failed to load recipes');
        }
    }
}

/**
 * Render recipes list in modal
 */
function renderRecipesList(recipes) {
    const modalBody = document.getElementById('recipesModalBody');
    
    if (!recipes || recipes.length === 0) {
        modalBody.innerHTML = '<p>No recipes found for this item</p>';
        return;
    }
    
    let html = `
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background-light border-b border-surface-border">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Recipe Name</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Recipe Date</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Total Used</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary whitespace-nowrap">Last Used</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border">
    `;
    
    recipes.forEach(recipe => {
        const recipeDate = recipe.recipe_date ? new Date(recipe.recipe_date).toLocaleDateString() : '-';
        const lastUsed = recipe.last_used ? new Date(recipe.last_used).toLocaleString() : '-';
        
        html += `
            <tr class="hover:bg-surface-border/20 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-text-primary whitespace-nowrap">${escapeHtml(recipe.recipe_name)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${recipeDate}</td>
                <td class="px-6 py-4 text-sm text-text-primary whitespace-nowrap font-mono">${formatNumber(recipe.total_used)}</td>
                <td class="px-6 py-4 text-sm text-text-secondary whitespace-nowrap">${lastUsed}</td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    modalBody.innerHTML = html;
}

/**
 * Close recipes modal
 */
function closeRecipesModal() {
    const modal = document.getElementById('recipesModal');
    modal.classList.add('hidden');
}

/**
 * Close modal when clicking outside
 */
window.onclick = function(event) {
    const modal = document.getElementById('recipesModal');
    if (event.target === modal || event.target.closest('.bg-black\\/50')) {
        closeRecipesModal();
    }
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return parseFloat(num).toLocaleString('en-US', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

