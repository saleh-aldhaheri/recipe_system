<!-- Page Header -->
<div class="page-header">
    <h1>Manage Ingredients</h1>
    <button class="btn btn-primary" onclick="openIngredientModal()">+ Add New Ingredient</button>
</div>

<!-- Filters -->
<div class="card">
    <div class="form-row">
        <div class="form-group">
            <label for="filterRecipe">Filter by Recipe</label>
            <select id="filterRecipe" onchange="applyFilters()">
                <option value="">All Recipes</option>
            </select>
        </div>
        <div class="form-group">
            <label for="filterItem">Filter by Item</label>
            <select id="filterItem" onchange="applyFilters()">
                <option value="">All Items</option>
            </select>
        </div>
    </div>
</div>

<!-- Ingredients Table -->
<div class="card">
    <div id="ingredientsTableContainer">
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading ingredients...</p>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="pagination" id="pagination"></div>

<!-- Ingredient Modal (for Create/Edit) -->
<div id="ingredientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="ingredientModalTitle">Add New Ingredient</h2>
            <button class="close" onclick="closeIngredientModal()">&times;</button>
        </div>
        <form id="ingredientForm" onsubmit="saveIngredient(event)">
            <input type="hidden" id="ingredientId">
            <input type="hidden" id="originalQuantity">
            <input type="hidden" id="originalItemId">
            
            <div class="form-group">
                <label for="ingredientRecipe">Recipe *</label>
                <select id="ingredientRecipe" required>
                    <option value="">Select Recipe</option>
                </select>
            </div>

            <div class="form-group">
                <label for="ingredientItem">Item *</label>
                <select id="ingredientItem" required onchange="checkItemBalance()">
                    <option value="">Select Item</option>
                </select>
                <small id="itemBalanceInfo" style="color: #7f8c8d; display: block; margin-top: 0.5rem;"></small>
            </div>

            <div class="form-group">
                <label for="ingredientQuantity">Quantity *</label>
                <input type="number" id="ingredientQuantity" step="0.001" min="0" required onchange="checkItemBalance()">
                <small style="color: #7f8c8d; display: block; margin-top: 0.5rem;">
                    This quantity will be deducted from the item's balance.
                </small>
            </div>

            <div id="balanceWarning" style="display: none; padding: 1rem; background: #fff3cd; border-radius: 4px; margin: 1rem 0; color: #856404;">
                <strong>Warning:</strong> The selected item's balance may be insufficient after this operation.
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeIngredientModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Ingredient</button>
            </div>
        </form>
    </div>
</div>
