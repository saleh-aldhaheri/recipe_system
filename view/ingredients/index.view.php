<!-- Breadcrumbs -->
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="<?= url('dashboard') ?>" class="text-text-secondary hover:text-primary transition-colors font-medium">Dashboard</a>
    <span class="material-symbols-outlined text-xs text-text-secondary">chevron_right</span>
    <span class="text-text-primary font-medium">Ingredients Management</span>
</div>

<!-- Page Heading & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight text-text-primary mb-2">Ingredients Management</h1>
        <p class="text-text-secondary text-sm">Manage your inventory items, units, and stock levels.</p>
    </div>
    <button onclick="openIngredientModal()" class="flex items-center justify-center gap-2 cursor-pointer overflow-hidden rounded-lg h-11 px-6 bg-primary hover:bg-primary-dark text-white text-sm font-bold shadow-md transition-all active:scale-95 whitespace-nowrap">
        <span class="material-symbols-outlined text-[20px]">add</span>
        <span class="truncate">Add Ingredient</span>
    </button>
</div>

<!-- Filters Section -->
<div class="bg-white border border-surface-border rounded-xl p-5 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row gap-5 items-end">
        <!-- Filter by Recipe -->
        <label class="flex flex-col w-full md:w-1/3 min-w-[200px]">
            <span class="text-text-secondary text-xs font-semibold uppercase tracking-wider mb-2 ml-1">Filter by Recipe</span>
            <div class="relative">
                <select id="filterRecipe" onchange="applyFilters()" class="w-full appearance-none rounded-lg border border-surface-border bg-white text-text-primary text-sm px-4 h-11 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
                    <option value="">All Recipes</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-text-secondary">
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
            </div>
        </label>
        <!-- Filter by Item -->
        <label class="flex flex-col w-full md:w-1/3 min-w-[200px]">
            <span class="text-text-secondary text-xs font-semibold uppercase tracking-wider mb-2 ml-1">Filter by Item</span>
            <div class="relative">
                <select id="filterItem" onchange="applyFilters()" class="w-full appearance-none rounded-lg border border-surface-border bg-white text-text-primary text-sm px-4 h-11 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
                    <option value="">All Items</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-text-secondary">
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
            </div>
        </label>
        <!-- Reset Button -->
        <button onclick="resetFilters()" class="h-11 px-5 rounded-lg border border-surface-border bg-white text-text-secondary hover:text-text-primary hover:border-primary hover:bg-background-light transition-all text-sm font-medium flex items-center gap-2 whitespace-nowrap shadow-sm">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
            Reset Filters
        </button>
    </div>
</div>

<!-- Data Table Section -->
<div class="bg-white border border-surface-border rounded-xl overflow-hidden shadow-lg flex flex-col">
    <div class="overflow-x-auto">
        <div id="ingredientsTableContainer">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading ingredients...</p>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="border-t border-surface-border bg-background-light p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
        <div id="pagination" class="flex items-center gap-2">
            <!-- Pagination will be inserted here by JavaScript -->
        </div>
    </div>
</div>

<!-- Ingredient Modal (for Create/Edit) -->
<div id="ingredientModal" class="hidden fixed z-50 inset-0 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeIngredientModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-surface-border">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 id="ingredientModalTitle" class="text-xl font-bold text-text-primary">Add New Ingredient</h2>
                <button onclick="closeIngredientModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="ingredientForm" onsubmit="saveIngredient(event)" class="px-6 py-6">
                <input type="hidden" id="ingredientId">
                <input type="hidden" id="originalQuantity">
                <input type="hidden" id="originalItemId">
                
                <div class="mb-5">
                    <label for="ingredientRecipe" class="block text-sm font-medium text-text-secondary mb-2">Recipe *</label>
                    <select id="ingredientRecipe" required class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
                        <option value="">Select Recipe</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label for="ingredientItem" class="block text-sm font-medium text-text-secondary mb-2">Item *</label>
                    <select id="ingredientItem" required onchange="checkItemBalance()" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
                        <option value="">Select Item</option>
                    </select>
                    <p id="itemBalanceInfo" class="mt-1 text-xs text-text-secondary"></p>
                </div>

                <div class="mb-5">
                    <label for="ingredientQuantity" class="block text-sm font-medium text-text-secondary mb-2">Quantity *</label>
                    <input type="number" id="ingredientQuantity" step="0.001" min="0" required onchange="checkItemBalance()" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                    <p class="mt-1 text-xs text-text-secondary">This quantity will be deducted from the item's balance.</p>
                </div>

                <div id="balanceWarning" class="hidden mb-5 p-4 rounded-lg bg-warning/20 border border-warning/50 text-warning text-sm">
                    <strong>Warning:</strong> The selected item's balance may be insufficient after this operation.
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-surface-border">
                    <button type="button" onclick="closeIngredientModal()" class="px-5 py-2.5 rounded-lg border border-surface-border bg-white text-text-secondary hover:bg-background-light hover:text-text-primary transition-all text-sm font-medium shadow-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white hover:bg-primary-dark transition-all text-sm font-bold shadow-md">
                        Save Ingredient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
