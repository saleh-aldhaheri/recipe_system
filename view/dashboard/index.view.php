<!-- Page Header -->
<div class="page-header">
    <h1>Dashboard</h1>
    <div class="time-frame-selector">
        <label for="timeFrameSelect">Time Frame:</label>
        <select id="timeFrameSelect" onchange="loadDashboardData()">
            <option value="last_week" selected>Last Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_year">Last Year</option>
        </select>
    </div>
</div>

<!-- Stats Widgets -->
<div class="stats-grid" id="statsWidgets">
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <div class="stat-label">Total Usage</div>
            <div class="stat-value" id="statTotalUsage">-</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-content">
            <div class="stat-label">Transactions</div>
            <div class="stat-value" id="statTransactions">-</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🍳</div>
        <div class="stat-content">
            <div class="stat-label">Recipes Used</div>
            <div class="stat-value" id="statRecipes">-</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-label">Active Items</div>
            <div class="stat-value" id="statActiveItems">-</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-grid">
    <!-- Pie Chart: Transactions by Type -->
    <div class="card chart-card">
        <div class="chart-header">
            <h3>Transactions by Type</h3>
            <select class="chart-time-frame" onchange="loadChartData('transactionsByType')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="transactionsByTypeChart"></canvas>
    </div>

    <!-- Bar Chart: Top Items -->
    <div class="card chart-card">
        <div class="chart-header">
            <h3>Top 10 Items by Usage</h3>
            <select class="chart-time-frame" onchange="loadChartData('topItems')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="topItemsChart"></canvas>
    </div>

    <!-- Line Chart: Daily Trend -->
    <div class="card chart-card">
        <div class="chart-header">
            <h3>Daily Usage Trend</h3>
            <select class="chart-time-frame" onchange="loadChartData('dailyTrend')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="dailyTrendChart"></canvas>
    </div>

    <!-- Bar Chart: Usage by Recipes -->
    <div class="card chart-card">
        <div class="chart-header">
            <h3>Top 10 Recipes by Usage</h3>
            <select class="chart-time-frame" onchange="loadChartData('usageByRecipes')">
                <option value="last_week">Last Week</option>
                <option value="last_month">Last Month</option>
                <option value="last_year">Last Year</option>
            </select>
        </div>
        <canvas id="usageByRecipesChart"></canvas>
    </div>
</div>

<!-- Items Summary Table -->
<div class="card">
    <div class="table-header">
        <h2>Items Summary</h2>
        <select class="table-time-frame" onchange="loadTableData()">
            <option value="last_week">Last Week</option>
            <option value="last_month">Last Month</option>
            <option value="last_year">Last Year</option>
        </select>
    </div>
    <div class="table-container">
        <div id="itemsTableContainer">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading items...</p>
            </div>
        </div>
    </div>
</div>

<!-- Recipes Modal (for View Recipes action) -->
<div id="recipesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="recipesModalTitle">Recipes for Item</h2>
            <button class="close" onclick="closeRecipesModal()">&times;</button>
        </div>
        <div id="recipesModalBody">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading recipes...</p>
            </div>
        </div>
    </div>
</div>

<style>
.time-frame-selector {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.time-frame-selector label {
    font-weight: 500;
    color: var(--text-primary);
}

.time-frame-selector select {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.875rem;
    cursor: pointer;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-primary);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: 8px;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    min-height: 350px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.chart-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.chart-time-frame,
.table-time-frame {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.875rem;
    cursor: pointer;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.table-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
}
</style>
