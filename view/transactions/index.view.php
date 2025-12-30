<!-- Page Header -->
<div class="page-header">
    <h1>Transactions History</h1>
    <div>
        <small style="color: var(--text-secondary);">Track all item balance changes and recipe usage</small>
    </div>
</div>

<!-- Search Box -->
<div class="search-box">
    <input type="text" id="searchInput" placeholder="Search by item name, recipe name, type, or operation..." onkeyup="handleSearch()">
</div>

<!-- Transactions Table -->
<div class="card">
    <div id="transactionsTableContainer">
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading transactions...</p>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="pagination" id="pagination"></div>
