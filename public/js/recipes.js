/**
 * Recipes Calendar Page
 * This file handles calendar display and recipe CRUD operations using AJAX
 */

// Current month and year
let currentDate = new Date();
let selectedDay = null;
let allRecipes = []; // Cache all recipes for the current month
let ingredientCounter = 0; // Counter for ingredient rows

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
    loadRecipesForMonth();
});

/**
 * Render calendar for current month
 */
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update month/year display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonthYear').textContent = `${monthNames[month]} ${year}`;

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Clear calendar
    const calendar = document.getElementById('calendar');
    calendar.innerHTML = '';

    // Add empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day';
        calendar.appendChild(emptyDay);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.onclick = () => openDayRecipes(year, month, day);

        const dayNumber = document.createElement('div');
        dayNumber.className = 'calendar-day-number';
        dayNumber.textContent = day;
        dayElement.appendChild(dayNumber);

        const recipesContainer = document.createElement('div');
        recipesContainer.className = 'calendar-day-recipes';
        recipesContainer.id = `recipes-${year}-${month}-${day}`;
        dayElement.appendChild(recipesContainer);

        calendar.appendChild(dayElement);
    }

    // Update recipes display for each day
    updateCalendarRecipes();
}

/**
 * Load all recipes for the current month
 */
async function loadRecipesForMonth() {
    try {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth() + 1;

        // Get all recipes (we'll filter by month on client side)
        // In a real app, you might want to add date filtering to the API
        const response = await apiGet('/recipe', {
            per_page: 1000 // Get many recipes
        });

        if (response.success) {
            allRecipes = response.data;
            updateCalendarRecipes();
        }
    } catch (error) {
        console.error('Error loading recipes:', error);
    }
}

/**
 * Update calendar to show recipes for each day
 */
function updateCalendarRecipes() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Clear all recipe displays
    for (let day = 1; day <= 31; day++) {
        const container = document.getElementById(`recipes-${year}-${month}-${day}`);
        if (container) {
            container.innerHTML = '';
        }
    }

    // Display recipes for each day
    allRecipes.forEach(recipe => {
        const recipeDate = new Date(recipe.date);
        if (recipeDate.getFullYear() === year && recipeDate.getMonth() === month) {
            const day = recipeDate.getDate();
            const container = document.getElementById(`recipes-${year}-${month}-${day}`);
            
            if (container) {
                const recipeItem = document.createElement('div');
                recipeItem.className = 'recipe-item';
                recipeItem.textContent = recipe.name;
                container.appendChild(recipeItem);

                // Mark day as having recipes
                const dayElement = container.parentElement;
                dayElement.classList.add('has-recipes');
            }
        }
    });
}

/**
 * Navigate to previous month
 */
function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    loadRecipesForMonth();
}

/**
 * Navigate to next month
 */
function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    loadRecipesForMonth();
}

/**
 * Open recipes for a specific day
 * @param {number} year
 * @param {number} month
 * @param {number} day
 */
async function openDayRecipes(year, month, day) {
    selectedDay = { year, month, day };
    const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    
    document.getElementById('selectedDate').textContent = dateString;
    document.getElementById('recipeModalTitle').textContent = `Recipes for ${dateString}`;
    document.getElementById('recipeModal').style.display = 'block';
    document.getElementById('recipeModalBody').innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading recipes...</p>
        </div>
    `;

    try {
        // Load recipes for this day
        // In MVC mode: use recipes.php, in API mode: use /recipe
        const response = await apiGet('/recipe', {
            per_page: 1000
        });

        if (response.success) {
            // Filter recipes for this date
            const dayRecipes = response.data.filter(recipe => {
                const recipeDate = new Date(recipe.date);
                return recipeDate.toISOString().split('T')[0] === dateString;
            });

            displayDayRecipes(dayRecipes, dateString);
        }
    } catch (error) {
        console.error('Error loading day recipes:', error);
        document.getElementById('recipeModalBody').innerHTML = `
            <div class="text-center" style="padding: 2rem; color: #e74c3c;">
                <p>Error: ${error.message}</p>
            </div>
        `;
    }
}

/**
 * Display recipes for a day
 * @param {Array} recipes
 * @param {string} dateString
 */
function displayDayRecipes(recipes, dateString) {
    let html = `
        <div style="margin-bottom: 1rem;">
            <button class="btn btn-primary" onclick="openRecipeForm('${dateString}')">+ Add New Recipe</button>
        </div>
    `;

    if (recipes.length === 0) {
        html += '<p>No recipes for this day. Click "Add New Recipe" to create one.</p>';
    } else {
        html += '<div style="display: flex; flex-direction: column; gap: 1rem;">';
        
        recipes.forEach(recipe => {
            html += `
                <div class="card">
                    <h3>${recipe.name}</h3>
                    <p style="color: #7f8c8d; margin: 0.5rem 0;">Date: ${recipe.date}</p>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <button class="btn btn-primary btn-small" onclick="editRecipe(${recipe.id})">Edit</button>
                        <button class="btn btn-danger btn-small" onclick="deleteRecipe(${recipe.id})">Delete</button>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    }

    document.getElementById('recipeModalBody').innerHTML = html;
}

/**
 * Close recipe modal
 */
function closeRecipeModal() {
    document.getElementById('recipeModal').style.display = 'none';
    loadRecipesForMonth(); // Refresh calendar
}

/**
 * Open recipe form for creating new recipe
 * @param {string} dateString
 */
function openRecipeForm(dateString) {
    closeRecipeModal();
    
    document.getElementById('recipeFormTitle').textContent = 'Add New Recipe';
    document.getElementById('recipeId').value = '';
    document.getElementById('recipeDate').value = dateString;
    document.getElementById('recipeDateInput').value = dateString;
    document.getElementById('recipeName').value = '';
    document.getElementById('ingredientsList').innerHTML = '';
    ingredientCounter = 0;
    
    document.getElementById('recipeFormModal').style.display = 'block';
}

/**
 * Close recipe form modal
 */
function closeRecipeFormModal() {
    document.getElementById('recipeFormModal').style.display = 'none';
    document.getElementById('recipeForm').reset();
    document.getElementById('ingredientsList').innerHTML = '';
    ingredientCounter = 0;
}

/**
 * Add ingredient row to recipe form
 */
async function addIngredientRow() {
    try {
        // Load items for dropdown
        const response = await apiGet('/items', { per_page: 1000 });
        
        if (!response.success) {
            showError('Failed to load items');
            return;
        }

        const items = response.data;
        const rowId = `ingredient-${ingredientCounter++}`;

        let html = `
            <div class="ingredient-item" id="${rowId}">
                <select class="ingredient-item-select" required>
                    <option value="">Select Item</option>
        `;

        items.forEach(item => {
            html += `<option value="${item.id}">${item.short_name} - ${item.name}</option>`;
        });

        html += `
                </select>
                <input type="number" class="ingredient-quantity" step="0.001" min="0" placeholder="Quantity" required>
                <button type="button" class="btn btn-danger btn-small" onclick="removeIngredientRow('${rowId}')">Remove</button>
            </div>
        `;

        const container = document.getElementById('ingredientsList');
        container.insertAdjacentHTML('beforeend', html);
    } catch (error) {
        console.error('Error loading items:', error);
        showError('Failed to load items');
    }
}

/**
 * Remove ingredient row
 * @param {string} rowId
 */
function removeIngredientRow(rowId) {
    document.getElementById(rowId).remove();
}

/**
 * Edit recipe
 * @param {number} id
 */
async function editRecipe(id) {
    try {
        closeRecipeModal();
        document.getElementById('recipeFormModal').style.display = 'block';
        document.getElementById('recipeFormTitle').textContent = 'Edit Recipe';
        document.getElementById('recipeId').value = id;

        // Load recipe details
        const response = await apiGet(`/recipe/${id}`);

        if (response.success) {
            const recipe = response.data;
            
            document.getElementById('recipeName').value = recipe.name;
            document.getElementById('recipeDate').value = recipe.date;
            document.getElementById('recipeDateInput').value = recipe.date;

            // Load items for ingredient dropdowns
            const itemsResponse = await apiGet('/items', { per_page: 1000 });
            const items = itemsResponse.data;

            // Clear and populate ingredients
            document.getElementById('ingredientsList').innerHTML = '';
            ingredientCounter = 0;

            if (recipe.ingredients && recipe.ingredients.length > 0) {
                recipe.ingredients.forEach(ingredient => {
                    const rowId = `ingredient-${ingredientCounter++}`;
                    let html = `
                        <div class="ingredient-item" id="${rowId}">
                            <select class="ingredient-item-select" required>
                                <option value="">Select Item</option>
                    `;

                    items.forEach(item => {
                        const selected = item.id === ingredient.item_id ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.short_name} - ${item.name}</option>`;
                    });

                    html += `
                            </select>
                            <input type="number" class="ingredient-quantity" step="0.001" min="0" 
                                   value="${ingredient.quantity}" placeholder="Quantity" required>
                            <button type="button" class="btn btn-danger btn-small" onclick="removeIngredientRow('${rowId}')">Remove</button>
                        </div>
                    `;

                    document.getElementById('ingredientsList').insertAdjacentHTML('beforeend', html);
                });
            }
        }
    } catch (error) {
        console.error('Error loading recipe:', error);
        showError(error.message || 'Failed to load recipe');
        closeRecipeFormModal();
    }
}

/**
 * Save recipe (Create or Update)
 * @param {Event} event
 */
async function saveRecipe(event) {
    event.preventDefault();

    try {
        const recipeId = document.getElementById('recipeId').value;
        const recipeData = {
            name: document.getElementById('recipeName').value.trim(),
            date: document.getElementById('recipeDateInput').value,
            ingredients: []
        };

        // Collect ingredients
        const ingredientRows = document.querySelectorAll('.ingredient-item');
        ingredientRows.forEach(row => {
            const itemId = row.querySelector('.ingredient-item-select').value;
            const quantity = parseFloat(row.querySelector('.ingredient-quantity').value);

            if (itemId && !isNaN(quantity)) {
                recipeData.ingredients.push({
                    item_id: parseInt(itemId),
                    quantity: quantity
                });
            }
        });

        let response;
        if (recipeId) {
            // Update recipe
            response = await apiPut(`/recipe/${recipeId}`, recipeData);
        } else {
            // Create recipe
            response = await apiPost('/recipe', recipeData);
        }

        if (response.success) {
            showSuccess(response.message || 'Recipe saved successfully');
            closeRecipeFormModal();
            loadRecipesForMonth();
        } else {
            showError(response.message || 'Failed to save recipe');
        }
    } catch (error) {
        console.error('Error saving recipe:', error);
        showError(error.message || 'Failed to save recipe');
    }
}

/**
 * Delete recipe
 * @param {number} id
 */
async function deleteRecipe(id) {
    if (!confirm('Are you sure you want to delete this recipe?')) {
        return;
    }

    try {
        const response = await apiDelete(`/recipe/${id}`);

        if (response.success) {
            showSuccess(response.message || 'Recipe deleted successfully');
            closeRecipeModal();
            loadRecipesForMonth();
        } else {
            showError(response.message || 'Failed to delete recipe');
        }
    } catch (error) {
        console.error('Error deleting recipe:', error);
        showError(error.message || 'Failed to delete recipe');
    }
}

/**
 * Open import modal
 */
function openImportModal() {
    document.getElementById('importModal').style.display = 'block';
    document.getElementById('importFiles').addEventListener('change', handleFileSelect);
}

/**
 * Close import modal
 */
function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
    document.getElementById('importForm').reset();
    document.getElementById('fileList').innerHTML = '';
    document.getElementById('importProgress').classList.add('hidden');
    document.getElementById('importResults').classList.add('hidden');
}

/**
 * Handle file selection
 */
function handleFileSelect(event) {
    const files = event.target.files;
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';

    if (files.length > 7) {
        showError('Maximum 7 files allowed');
        event.target.value = '';
        return;
    }

    Array.from(files).forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <span>${index + 1}. ${file.name} (${(file.size / 1024).toFixed(2)} KB)</span>
        `;
        fileList.appendChild(fileItem);
    });
}

/**
 * Import recipes from Excel files
 * @param {Event} event
 */
async function importRecipes(event) {
    event.preventDefault();

    const fileInput = document.getElementById('importFiles');
    const files = fileInput.files;

    if (files.length === 0) {
        showError('Please select at least one file');
        return;
    }

    if (files.length > 7) {
        showError('Maximum 7 files allowed');
        return;
    }

    try {
        // Show progress
        document.getElementById('importProgress').classList.remove('hidden');
        document.getElementById('importResults').classList.add('hidden');

        // Create FormData for file upload
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        // Make AJAX POST request with FormData
        const response = await apiUpload('/recipe/import', formData);

        // Hide progress, show results
        document.getElementById('importProgress').classList.add('hidden');
        document.getElementById('importResults').classList.remove('hidden');

        if (response.success) {
            let resultsHtml = '<h3>Import Results</h3>';
            resultsHtml += `<p><strong>Processed:</strong> ${response.data.processed} file(s)</p>`;

            if (response.data.recipes && response.data.recipes.length > 0) {
                resultsHtml += '<div style="margin-top: 1rem;">';
                response.data.recipes.forEach(result => {
                    resultsHtml += `
                        <div class="card" style="margin-bottom: 1rem;">
                            <p><strong>File:</strong> ${result.file}</p>
                            <p><strong>Status:</strong> <span style="color: ${result.status === 'success' ? '#27ae60' : '#e74c3c'}">${result.status}</span></p>
                    `;

                    if (result.status === 'success') {
                        resultsHtml += `
                            <p><strong>Recipe:</strong> ${result.recipe_name}</p>
                            <p><strong>Ingredients:</strong> ${result.ingredients_count}</p>
                        `;
                    } else {
                        resultsHtml += `<p><strong>Error:</strong> ${result.message}</p>`;
                    }

                    if (result.failed_ingredients && result.failed_ingredients.length > 0) {
                        resultsHtml += '<p><strong>Auto-created Items:</strong></p><ul>';
                        result.failed_ingredients.forEach(item => {
                            resultsHtml += `<li>${item.short_name} - ${item.reason}</li>`;
                        });
                        resultsHtml += '</ul>';
                    }

                    resultsHtml += '</div>';
                });
                resultsHtml += '</div>';
            }

            document.getElementById('importResults').innerHTML = resultsHtml;
            showSuccess('Import completed');
            
            // Reload recipes
            loadRecipesForMonth();
        } else {
            showError(response.message || 'Import failed');
        }
    } catch (error) {
        console.error('Error importing recipes:', error);
        document.getElementById('importProgress').classList.add('hidden');
        showError(error.message || 'Failed to import recipes');
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['recipeModal', 'recipeFormModal', 'importModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            if (modalId === 'recipeModal') closeRecipeModal();
            if (modalId === 'recipeFormModal') closeRecipeFormModal();
            if (modalId === 'importModal') closeImportModal();
        }
    });
}

