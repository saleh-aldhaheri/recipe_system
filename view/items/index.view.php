<!-- Page Header -->
<div class="page-header">
    <h1>Manage Items</h1>
    <button class="btn btn-primary" onclick="openItemModal()">+ Add New Item</button>
</div>

<!-- Search Box -->
<div class="search-box">
    <input type="text" id="searchInput" placeholder="Search items by name or short name..." onkeyup="handleSearch()">
</div>

<!-- Items Table -->
<div class="card">
    <div id="itemsTableContainer">
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading items...</p>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="pagination" id="pagination"></div>

<!-- Item Modal (for Create/Edit) -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Item</h2>
            <button class="close" onclick="closeItemModal()">&times;</button>
        </div>
        <form id="itemForm" onsubmit="saveItem(event)">
            <input type="hidden" id="itemId">
            
            <div class="form-group">
                <label for="itemName">Name *</label>
                <input type="text" id="itemName" required minlength="3" maxlength="255">
            </div>

            <div class="form-group">
                <label for="itemShortName">Short Name *</label>
                <input type="text" id="itemShortName" required maxlength="50">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="itemBalance">Balance *</label>
                    <input type="number" id="itemBalance" step="0.001" min="0" required>
                </div>

                <div class="form-group">
                    <label for="itemUnit">Unit *</label>
                    <input type="text" id="itemUnit" required maxlength="20">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>
