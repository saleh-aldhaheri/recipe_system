<!-- Breadcrumbs -->
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="<?= url('') ?>" class="text-text-secondary hover:text-primary transition-colors font-medium">Home</a>
    <span class="material-symbols-outlined text-xs text-text-secondary">chevron_right</span>
    <span class="text-text-primary font-medium">Dashboard</span>
</div>

<!-- Page Heading & Time Frame Selector -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
    <div>
        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight text-text-primary mb-2">Dashboard</h1>
        <p class="text-text-secondary text-base">Overview of your recipe management system</p>
    </div>
    <div class="flex items-center gap-3">
        <label for="timeFrameSelect" class="text-sm font-medium text-text-secondary">Time Frame:</label>
        <select id="timeFrameSelect" onchange="loadDashboardData()" class="px-4 py-2.5 rounded-lg border border-surface-border bg-white text-text-primary text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
            <option value="last_week" selected>Last Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_year">Last Year</option>
        </select>
    </div>
</div>

<!-- Stats Widgets -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
        <div class="flex items-center gap-4">
            <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-2xl">trending_up</span>
            </div>
            <div class="flex-1">
                <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Total Usage</div>
                <div class="text-2xl font-bold text-text-primary" id="statTotalUsage">-</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
        <div class="flex items-center gap-4">
            <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-2xl">receipt_long</span>
            </div>
            <div class="flex-1">
                <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Transactions</div>
                <div class="text-2xl font-bold text-text-primary" id="statTransactions">-</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
        <div class="flex items-center gap-4">
            <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-2xl">restaurant_menu</span>
            </div>
            <div class="flex-1">
                <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Recipes Used</div>
                <div class="text-2xl font-bold text-text-primary" id="statRecipes">-</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
        <div class="flex items-center gap-4">
            <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined text-2xl">inventory_2</span>
            </div>
            <div class="flex-1">
                <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Active Items</div>
                <div class="text-2xl font-bold text-text-primary" id="statActiveItems">-</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Pie Chart: Transactions by Type -->
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border">
            <h3 class="text-lg font-bold text-text-primary">Transactions by Type</h3>
            <select class="px-3 py-1.5 rounded-lg border border-surface-border bg-white text-text-primary text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary" onchange="loadChartData('transactionsByType')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="transactionsByTypeChart" class="w-full"></canvas>
    </div>

    <!-- Bar Chart: Top Items -->
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border">
            <h3 class="text-lg font-bold text-text-primary">Top 10 Items by Usage</h3>
            <select class="px-3 py-1.5 rounded-lg border border-surface-border bg-white text-text-primary text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary" onchange="loadChartData('topItems')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="topItemsChart" class="w-full"></canvas>
    </div>

    <!-- Line Chart: Daily Trend -->
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border">
            <h3 class="text-lg font-bold text-text-primary">Daily Usage Trend</h3>
            <select class="px-3 py-1.5 rounded-lg border border-surface-border bg-white text-text-primary text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary" onchange="loadChartData('dailyTrend')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="dailyTrendChart" class="w-full"></canvas>
    </div>

    <!-- Bar Chart: Usage by Recipes -->
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-border">
            <h3 class="text-lg font-bold text-text-primary">Top 10 Recipes by Usage</h3>
            <select class="px-3 py-1.5 rounded-lg border border-surface-border bg-white text-text-primary text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary" onchange="loadChartData('usageByRecipes')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="usageByRecipesChart" class="w-full"></canvas>
    </div>
</div>

<!-- Items Summary Table -->
<div class="bg-white border border-surface-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-5 border-b border-surface-border flex items-center justify-between">
        <h2 class="text-xl font-bold text-text-primary">Items Summary</h2>
        <select class="px-3 py-1.5 rounded-lg border border-surface-border bg-white text-text-primary text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary" onchange="loadTableData()">
            <option value="last_week">Last Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_year">Last Year</option>
        </select>
    </div>
    <div class="overflow-x-auto">
        <div id="itemsTableContainer">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading items...</p>
            </div>
        </div>
    </div>
</div>

<!-- Recipes Modal (for View Recipes action) -->
<div id="recipesModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeRecipesModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-border">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 id="recipesModalTitle" class="text-xl font-bold text-text-primary">Recipes for Item</h2>
                <button onclick="closeRecipesModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="recipesModalBody" class="px-6 py-6">
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                    <p class="mt-4 text-text-secondary">Loading recipes...</p>
                </div>
            </div>
        </div>
    </div>
</div>
