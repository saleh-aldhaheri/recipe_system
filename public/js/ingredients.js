/**
 * Ingredients Management Page
 * This file handles all CRUD operations for ingredients using AJAX
 * When creating/updating ingredients, the quantity is deducted from item balance
 */

// Current page state
let currentPage = 1;
let currentPerPage = 10;
let currentRecipeFilter = '';
let currentItemFilter = '';
let allRecipes = [];
let allItems = [];
let choicesInstances = {}; // Store Choices.js instances

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    loadRecipes();
    loadItems();
    loadIngredients();
});

/**
 * Load all recipes for dropdown
 */
async function loadRecipes() {
    try {
        const response = await apiGet('/recipes', { per_page: 1000 });
        if (response.success) {
            allRecipes = response.data;
            populateRecipeDropdowns();
        }
    } catch (error) {
        console.error('Error loading recipes:', error);
    }
}

/**
 * Load all items for dropdown
 */
async function loadItems() {
    try {
        const response = await apiGet('/items', { per_page: 1000 });
        if (response.success) {
            allItems = response.data;
            populateItemDropdowns();
        }
    } catch (error) {
        console.error('Error loading items:', error);
    }
}

/**
 * Initialize Choices.js for a select element
 */
function initChoices(selectId, options = {}) {
    const select = document.getElementById(selectId);
    if (!select) return null;
    
    // Destroy existing instance if any
    if (choicesInstances[selectId]) {
        choicesInstances[selectId].destroy();
    }
    
    const defaultOptions = {
        searchEnabled: true,
        shouldSort: true,
        placeholder: true,
        placeholderValue: select.querySelector('option[value=""]')?.textContent || 'Select...',
        searchPlaceholderValue: 'Search...',
        ...options
    };
    
    choicesInstances[selectId] = new Choices(select, defaultOptions);
    return choicesInstances[selectId];
}

/**
 * Populate recipe dropdowns
 */
function populateRecipeDropdowns() {
    const filterSelect = document.getElementById('filterRecipe');
    const formSelect = document.getElementById('ingredientRecipe');

    // Clear existing options (except first one)
    filterSelect.innerHTML = '<option value="">All Recipes</option>';
    formSelect.innerHTML = '<option value="">Select Recipe</option>';

    allRecipes.forEach(recipe => {
        const option1 = document.createElement('option');
        option1.value = recipe.id;
        option1.textContent = `${recipe.name} (${recipe.date})`;
        filterSelect.appendChild(option1);

        const option2 = document.createElement('option');
        option2.value = recipe.id;
        option2.textContent = `${recipe.name} (${recipe.date})`;
        formSelect.appendChild(option2);
    });
    
    // Initialize Choices.js
    initChoices('filterRecipe', { placeholderValue: 'All Recipes' });
    initChoices('ingredientRecipe', { placeholderValue: 'Select Recipe' });
}

/**
 * Populate item dropdowns
 */
function populateItemDropdowns() {
    const filterSelect = document.getElementById('filterItem');
    const formSelect = document.getElementById('ingredientItem');

    // Clear existing options (except first one)
    filterSelect.innerHTML = '<option value="">All Items</option>';
    formSelect.innerHTML = '<option value="">Select Item</option>';

    allItems.forEach(item => {
        const option1 = document.createElement('option');
        option1.value = item.id;
        option1.textContent = `${item.short_name} - ${item.name} (Balance: ${item.balance} ${item.unit})`;
        filterSelect.appendChild(option1);

        const option2 = document.createElement('option');
        option2.value = item.id;
        option2.textContent = `${item.short_name} - ${item.name} (Balance: ${item.balance} ${item.unit})`;
        option2.dataset.balance = item.balance;
        formSelect.appendChild(option2);
    });
    
    // Initialize Choices.js
    initChoices('filterItem', { placeholderValue: 'All Items' });
    initChoices('ingredientItem', { placeholderValue: 'Select Item' });
}

/**
 * Load ingredients from API
 */
async function loadIngredients() {
    try {
        document.getElementById('ingredientsTableContainer').innerHTML = `
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading ingredients...</p>
            </div>
        `;

        const params = {
            page: currentPage,
            per_page: currentPerPage
        };

        if (currentRecipeFilter) {
            params.recipe_id = currentRecipeFilter;
        }

        if (currentItemFilter) {
            params.item_id = currentItemFilter;
        }

        const response = await apiGet('/ingredients', params);

        if (response.success) {
            displayIngredients(response.data);
            displayPagination(response.pagination);
        } else {
            showError('Failed to load ingredients');
        }
    } catch (error) {
        console.error('Error loading ingredients:', error);
        showError(error.message || 'Failed to load ingredients');
        
        document.getElementById('ingredientsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem; color: #e74c3c;">
                <p>Error: ${error.message}</p>
                <button class="btn btn-primary mt-1" onclick="loadIngredients()">Retry</button>
            </div>
        `;
    }
}

/**
 * Display ingredients in a table
 * @param {Array} ingredients
 */
function displayIngredients(ingredients) {
    if (ingredients.length === 0) {
        document.getElementById('ingredientsTableContainer').innerHTML = `
            <div class="text-center" style="padding: 2rem;">
                <p>No ingredients found</p>
            </div>
        `;
        return;
    }

    let html = `
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-background-light border-b border-surface-border">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary min-w-[180px]">Recipe Name</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary min-w-[150px]">Item Name</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary w-32">Quantity</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary w-32">Unit</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-text-secondary w-24 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-border text-sm">
    `;

    ingredients.forEach((ingredient, index) => {
        const rowNumber = (currentPage - 1) * currentPerPage + index + 1;
        html += `
            <tr class="group hover:bg-surface-border/20 transition-colors">
                <td class="py-4 px-6 text-text-primary font-medium">${ingredient.recipe ? escapeHtml(ingredient.recipe.name) : 'N/A'}</td>
                <td class="py-4 px-6 text-text-secondary">${ingredient.item ? escapeHtml(ingredient.item.name) : 'N/A'}</td>
                <td class="py-4 px-6 text-text-primary font-mono">${parseFloat(ingredient.quantity).toFixed(3)}</td>
                <td class="py-4 px-6">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                        ${ingredient.item ? escapeHtml(ingredient.item.unit) : 'N/A'}
                    </span>
                </td>
                <td class="py-4 px-6 text-right">
                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                        <button onclick="editIngredient(${ingredient.id})" class="p-1.5 rounded-md hover:bg-background-light text-text-secondary hover:text-primary transition-colors" title="Edit">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button onclick="deleteIngredient(${ingredient.id})" class="p-1.5 rounded-md hover:bg-background-light text-text-secondary hover:text-danger transition-colors" title="Delete">
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

    document.getElementById('ingredientsTableContainer').innerHTML = html;
}

/**
 * Display pagination controls
 * @param {Object} pagination
 */
function displayPagination(pagination) {
    if (!pagination || pagination.last_page <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    let html = '';

    // Show page info
    html += `
        <span class="text-text-secondary">Showing <span class="font-medium text-text-primary">${((pagination.current_page - 1) * pagination.per_page) + 1}</span> to <span class="font-medium text-text-primary">${Math.min(pagination.current_page * pagination.per_page, pagination.total)}</span> of <span class="font-medium text-text-primary">${pagination.total}</span> results</span>
    `;
    
    // Pagination buttons
    html += `<div class="flex items-center gap-2 ml-4">`;
    
    html += `
        <button ${pagination.current_page === 1 ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page - 1})"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-text-secondary ring-1 ring-inset ring-surface-border hover:bg-background-light focus:z-20 focus:outline-offset-0 transition-colors ${pagination.current_page === 1 ? 'cursor-not-allowed opacity-50' : ''}">
            <span class="sr-only">Previous</span>
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>
    `;

    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === 1 || i === pagination.last_page || 
            (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            const isActive = i === pagination.current_page;
            html += `
                <button onclick="goToPage(${i})"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ${isActive ? 'text-white bg-primary focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary' : 'text-text-secondary ring-1 ring-inset ring-surface-border hover:bg-background-light hover:text-text-primary focus:z-20 focus:outline-offset-0 transition-colors'}">
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += `<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-text-secondary ring-1 ring-inset ring-surface-border focus:outline-offset-0">...</span>`;
        }
    }

    html += `
        <button ${pagination.current_page === pagination.last_page ? 'disabled' : ''} 
                onclick="goToPage(${pagination.current_page + 1})"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-text-secondary ring-1 ring-inset ring-surface-border hover:bg-background-light focus:z-20 focus:outline-offset-0 transition-colors ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed opacity-50' : ''}">
            <span class="sr-only">Next</span>
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </button>
    `;
    
    html += `</div>`;

    document.getElementById('pagination').innerHTML = html;
}

/**
 * Navigate to specific page
 * @param {number} page
 */
function goToPage(page) {
    currentPage = page;
    loadIngredients();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Apply filters
 */
function applyFilters() {
    // Get values from Choices.js instances if they exist
    const filterRecipeInstance = choicesInstances['filterRecipe'];
    const filterItemInstance = choicesInstances['filterItem'];
    
    currentRecipeFilter = filterRecipeInstance ? filterRecipeInstance.getValue(true) : document.getElementById('filterRecipe').value;
    currentItemFilter = filterItemInstance ? filterItemInstance.getValue(true) : document.getElementById('filterItem').value;
    currentPage = 1;
    loadIngredients();
}

/**
 * Reset filters
 */
function resetFilters() {
    // Reset Choices.js instances if they exist
    if (choicesInstances['filterRecipe']) {
        choicesInstances['filterRecipe'].setChoiceByValue('');
    }
    if (choicesInstances['filterItem']) {
        choicesInstances['filterItem'].setChoiceByValue('');
    }
    
    // Reset native selects if Choices.js is not used
    const filterRecipe = document.getElementById('filterRecipe');
    const filterItem = document.getElementById('filterItem');
    if (filterRecipe && !choicesInstances['filterRecipe']) {
        filterRecipe.value = '';
    }
    if (filterItem && !choicesInstances['filterItem']) {
        filterItem.value = '';
    }
    
    currentRecipeFilter = '';
    currentItemFilter = '';
    currentPage = 1;
    loadIngredients();
}

/**
 * Open modal for creating new ingredient
 */
function openIngredientModal() {
    const modal = document.getElementById('ingredientModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'block';
    }
    document.getElementById('ingredientForm').reset();
    document.getElementById('ingredientId').value = '';
    document.getElementById('originalQuantity').value = '';
    document.getElementById('originalItemId').value = '';
    document.getElementById('ingredientModalTitle').textContent = 'Add New Ingredient';
    document.getElementById('balanceWarning').classList.add('hidden');
    document.getElementById('itemBalanceInfo').textContent = '';
}

/**
 * Close ingredient modal
 */
function closeIngredientModal() {
    const modal = document.getElementById('ingredientModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
    document.getElementById('ingredientForm').reset();
    document.getElementById('balanceWarning').classList.add('hidden');
    document.getElementById('itemBalanceInfo').textContent = '';
}

/**
 * Check item balance and show warning if needed
 */
function checkItemBalance() {
    // Get value from Choices.js instance if it exists
    const ingredientItemInstance = choicesInstances['ingredientItem'];
    const itemId = ingredientItemInstance ? ingredientItemInstance.getValue(true) : document.getElementById('ingredientItem').value;
    const quantity = parseFloat(document.getElementById('ingredientQuantity').value) || 0;
    const originalQuantity = parseFloat(document.getElementById('originalQuantity').value) || 0;
    const originalItemId = document.getElementById('originalItemId').value;
    const warningDiv = document.getElementById('balanceWarning');
    const infoSpan = document.getElementById('itemBalanceInfo');

    if (!itemId) {
        infoSpan.textContent = '';
        warningDiv.classList.add('hidden');
        return;
    }

    // Get selected option from Choices.js or native select
    let selectedOption;
    if (ingredientItemInstance) {
        const selectedValue = ingredientItemInstance.getValue(true);
        selectedOption = Array.from(document.getElementById('ingredientItem').options).find(opt => opt.value === selectedValue);
    } else {
        selectedOption = document.getElementById('ingredientItem').selectedOptions[0];
    }
    const currentBalance = parseFloat(selectedOption?.dataset.balance) || 0;

    // Calculate balance after operation
    let balanceAfter = currentBalance;
    if (document.getElementById('ingredientId').value) {
        // Editing: add back original quantity if same item, then subtract new
        if (originalItemId === itemId) {
            balanceAfter = currentBalance + originalQuantity - quantity;
        } else {
            // Different item: subtract new quantity
            balanceAfter = currentBalance - quantity;
        }
    } else {
        // Creating: subtract quantity
        balanceAfter = currentBalance - quantity;
    }

    infoSpan.textContent = `Current Balance: ${currentBalance} | After: ${balanceAfter}`;

    if (balanceAfter < 0) {
        warningDiv.classList.remove('hidden');
        warningDiv.innerHTML = `<strong>Warning:</strong> The selected item's balance will be negative (${balanceAfter.toFixed(3)}) after this operation.`;
    } else {
        warningDiv.classList.add('hidden');
    }
}

/**
 * Edit ingredient - Load ingredient data and open modal
 * @param {number} id
 */
async function editIngredient(id) {
    try {
        document.getElementById('ingredientModalTitle').textContent = 'Loading...';
        document.getElementById('ingredientModal').classList.remove('hidden');

        const response = await apiGet(`/ingredients/${id}`);

        if (response.success) {
            const ingredient = response.data;

            document.getElementById('ingredientId').value = ingredient.id;
            document.getElementById('ingredientRecipe').value = ingredient.recipe_id;
            document.getElementById('ingredientItem').value = ingredient.item_id;
            document.getElementById('ingredientQuantity').value = ingredient.quantity;
            document.getElementById('originalQuantity').value = ingredient.quantity;
            document.getElementById('originalItemId').value = ingredient.item_id;
            document.getElementById('ingredientModalTitle').textContent = 'Edit Ingredient';

            checkItemBalance();
        } else {
            showError('Failed to load ingredient');
            closeIngredientModal();
        }
    } catch (error) {
        console.error('Error loading ingredient:', error);
        showError(error.message || 'Failed to load ingredient');
        closeIngredientModal();
    }
}

/**
 * Save ingredient (Create or Update)
 * When saving, we need to update the item's balance
 * @param {Event} event
 */
async function saveIngredient(event) {
    event.preventDefault();

    try {
        // Get values from Choices.js instances if they exist
        const ingredientRecipeInstance = choicesInstances['ingredientRecipe'];
        const ingredientItemInstance = choicesInstances['ingredientItem'];
        
        const ingredientId = document.getElementById('ingredientId').value;
        const originalQuantity = parseFloat(document.getElementById('originalQuantity').value) || 0;
        const originalItemId = document.getElementById('originalItemId').value;
        const newItemId = parseInt(ingredientItemInstance ? ingredientItemInstance.getValue(true) : document.getElementById('ingredientItem').value);
        const newQuantity = parseFloat(document.getElementById('ingredientQuantity').value);

        const ingredientData = {
            recipe_id: parseInt(ingredientRecipeInstance ? ingredientRecipeInstance.getValue(true) : document.getElementById('ingredientRecipe').value),
            item_id: newItemId,
            quantity: newQuantity
        };

        let response;

        if (ingredientId) {
            // Update ingredient
            response = await apiPut(`/ingredients/${ingredientId}`, ingredientData);
            
            // Item balance is automatically updated by Ingredient model boot events
            // No need to manually update via API
        } else {
            // Create ingredient
            response = await apiPost('/ingredients', ingredientData);
            
            // Item balance is automatically updated by Ingredient model boot events
            // No need to manually update via API
        }

        if (response.success) {
            showSuccess(response.message || (ingredientId ? 'Ingredient updated successfully' : 'Ingredient created successfully'));
            closeIngredientModal();
            loadIngredients();
            loadItems(); // Reload items to get updated balances
        } else {
            showError(response.message || 'Failed to save ingredient');
        }
    } catch (error) {
        console.error('Error saving ingredient:', error);
        showError(error.message || 'Failed to save ingredient');
    }
}

/**
 * Deduct quantity from item balance (for new ingredients)
 * @param {number} itemId
 * @param {number} quantity
 */
async function deductFromItemBalance(itemId, quantity) {
    try {
        // Get current item
        const itemResponse = await apiGet(`/items/${itemId}`);
        if (itemResponse.success) {
            const item = itemResponse.data;
            const newBalance = Math.max(0, item.balance - quantity); // Prevent negative

            // Update item balance
            await apiPatch(`/items/${itemId}`, {
                balance: newBalance
            });
        }
    } catch (error) {
        console.error('Error updating item balance:', error);
        // Don't throw - this is a side effect, ingredient was already created
    }
}

/**
 * Update item balance when editing ingredient
 * @param {number} originalItemId
 * @param {number} originalQuantity
 * @param {number} newItemId
 * @param {number} newQuantity
 */
async function updateItemBalance(originalItemId, originalQuantity, newItemId, newQuantity) {
    try {
        // If same item, adjust balance
        if (originalItemId === newItemId) {
            const itemResponse = await apiGet(`/items/${originalItemId}`);
            if (itemResponse.success) {
                const item = itemResponse.data;
                // Add back original quantity, subtract new quantity
                const newBalance = Math.max(0, item.balance + originalQuantity - newQuantity);

                await apiPatch(`/items/${originalItemId}`, {
                    balance: newBalance
                });
            }
        } else {
            // Different item: add back to original, deduct from new
            // Add back to original item
            const originalItemResponse = await apiGet(`/items/${originalItemId}`);
            if (originalItemResponse.success) {
                const originalItem = originalItemResponse.data;
                const newOriginalBalance = originalItem.balance + originalQuantity;
                await apiPatch(`/items/${originalItemId}`, {
                    balance: newOriginalBalance
                });
            }

            // Deduct from new item
            const newItemResponse = await apiGet(`/items/${newItemId}`);
            if (newItemResponse.success) {
                const newItem = newItemResponse.data;
                const newItemBalance = Math.max(0, newItem.balance - newQuantity);
                await apiPatch(`/items/${newItemId}`, {
                    balance: newItemBalance
                });
            }
        }
    } catch (error) {
        console.error('Error updating item balances:', error);
        // Don't throw - ingredient was already updated
    }
}

/**
 * Delete ingredient
 * When deleting, we need to add back the quantity to item balance
 * @param {number} id
 */
async function deleteIngredient(id) {
    const confirmed = await confirmAction('Are you sure you want to delete this ingredient? The quantity will be added back to the item balance.', 'Delete Ingredient');
    if (!confirmed) {
        return;
    }

    try {
        // Get ingredient details first
        const ingredientResponse = await apiGet(`/ingredients/${id}`);
        
        if (!ingredientResponse.success) {
            showError('Failed to load ingredient details');
            return;
        }

        const ingredient = ingredientResponse.data;

        // Delete ingredient
        const response = await apiDelete(`/ingredients/${id}`);

        if (response.success) {
            // Item balance is automatically updated by Ingredient model boot events
            // No need to manually update via API
            
            showSuccess(response.message || 'Ingredient deleted successfully');
            loadIngredients();
            loadItems(); // Reload items to show updated balances
        } else {
            showError(response.message || 'Failed to delete ingredient');
        }
    } catch (error) {
        console.error('Error deleting ingredient:', error);
        showError(error.message || 'Failed to delete ingredient');
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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('ingredientModal');
    if (event.target === modal || event.target.closest('.bg-black\\/50')) {
        closeIngredientModal();
    }
}

