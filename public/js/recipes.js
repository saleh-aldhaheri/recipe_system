/**
 * Recipes Calendar Page - Weekly View
 * This file handles weekly calendar display and recipe CRUD operations using AJAX
 */

// Current week start date (Monday)
let currentWeekStart = getMonday(new Date());
let selectedDay = null;
let allRecipes = []; // Cache all recipes
let ingredientCounter = 0; // Counter for ingredient rows

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
    loadRecipes();
    
    // Add click event listener to close modal when clicking outside
    const recipeModal = document.getElementById('recipeModal');
    if (recipeModal) {
        recipeModal.addEventListener('click', function(event) {
            if (event.target === recipeModal) {
                closeRecipeModal();
            }
        });
    }
    
    // Add click event listener to recipe form modal
    const recipeFormModal = document.getElementById('recipeFormModal');
    if (recipeFormModal) {
        recipeFormModal.addEventListener('click', function(event) {
            if (event.target === recipeFormModal) {
                closeRecipeFormModal();
            }
        });
    }
});

/**
 * Get Monday of the week for a given date
 */
function getMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
    return new Date(d.setDate(diff));
}

/**
 * Render weekly calendar
 */
function renderCalendar() {
    const weekStart = new Date(currentWeekStart);
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);

    // Update week display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    const startMonth = monthNames[weekStart.getMonth()];
    const endMonth = monthNames[weekEnd.getMonth()];
    
    let weekText = `${startMonth} ${weekStart.getDate()}`;
    if (weekStart.getMonth() !== weekEnd.getMonth() || weekStart.getFullYear() !== weekEnd.getFullYear()) {
        weekText += ` - ${endMonth} ${weekEnd.getDate()}, ${weekEnd.getFullYear()}`;
    } else {
        weekText += ` - ${weekEnd.getDate()}, ${weekStart.getFullYear()}`;
    }
    
    document.getElementById('currentMonthYear').textContent = weekText;

    // Clear calendar
    const calendar = document.getElementById('calendar');
    calendar.innerHTML = '';

    // Add days of the week (Monday to Sunday)
    const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    for (let i = 0; i < 7; i++) {
        const dayDate = new Date(weekStart);
        dayDate.setDate(dayDate.getDate() + i);
        
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        // Add today class
        const today = new Date();
        if (dayDate.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        // Click handler
        dayElement.onclick = () => openDayRecipes(dayDate);

        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.innerHTML = `
            <div class="day-name">${dayNames[i]}</div>
            <div class="day-number">${dayDate.getDate()}</div>
        `;
        dayElement.appendChild(dayHeader);

        const recipesContainer = document.createElement('div');
        recipesContainer.className = 'calendar-day-recipes';
        recipesContainer.id = `recipes-${dayDate.toISOString().split('T')[0]}`;
        dayElement.appendChild(recipesContainer);

        calendar.appendChild(dayElement);
    }

    // Update recipes display
    updateCalendarRecipes();
}

/**
 * Load all recipes
 */
async function loadRecipes() {
    try {
        const response = await apiGet('/recipes', {
            per_page: 1000
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
    const weekStart = new Date(currentWeekStart);
    
    for (let i = 0; i < 7; i++) {
        const dayDate = new Date(weekStart);
        dayDate.setDate(dayDate.getDate() + i);
        const dateString = dayDate.toISOString().split('T')[0];
        
        const container = document.getElementById(`recipes-${dateString}`);
        if (!container) continue;
        
        // Find recipes for this day
        const dayRecipes = allRecipes.filter(recipe => {
            const recipeDate = new Date(recipe.date);
            return recipeDate.toISOString().split('T')[0] === dateString;
        });
        
        container.innerHTML = '';
        
        if (dayRecipes.length > 0) {
            dayRecipes.forEach(recipe => {
                const recipeBadge = document.createElement('div');
                recipeBadge.className = 'recipe-badge';
                recipeBadge.textContent = recipe.name;
                recipeBadge.title = recipe.name;
                container.appendChild(recipeBadge);
            });
        }
    }
}

/**
 * Navigate to previous week
 */
function previousWeek() {
    currentWeekStart = new Date(currentWeekStart);
    currentWeekStart.setDate(currentWeekStart.getDate() - 7);
    renderCalendar();
    loadRecipes();
}

/**
 * Navigate to next week
 */
function nextWeek() {
    currentWeekStart = new Date(currentWeekStart);
    currentWeekStart.setDate(currentWeekStart.getDate() + 7);
    renderCalendar();
    loadRecipes();
}

/**
 * Open recipes for a specific day
 * @param {Date} dayDate
 */
async function openDayRecipes(dayDate) {
    selectedDay = dayDate;
    const dateString = dayDate.toISOString().split('T')[0];
    
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
        const response = await apiGet('/recipes', {
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
        // Since only one recipe per date is allowed, show the recipe
        const recipe = recipes[0];
        html += `
            <div class="card">
                <h3>${recipe.name}</h3>
                <p style="color: #7f8c8d; margin: 0.5rem 0;">Date: ${recipe.date}</p>
                ${recipe.ingredients && recipe.ingredients.length > 0 ? `
                    <div style="margin-top: 1rem;">
                        <strong>Ingredients:</strong>
                        <ul style="margin-top: 0.5rem;">
                            ${recipe.ingredients.map(ing => 
                                `<li>${ing.item ? ing.item.name : 'N/A'} - ${ing.quantity}</li>`
                            ).join('')}
                        </ul>
                    </div>
                ` : ''}
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                    <button class="btn btn-primary btn-small" onclick="editRecipe(${recipe.id})">Edit</button>
                    <button class="btn btn-danger btn-small" onclick="deleteRecipe(${recipe.id})">Delete</button>
                </div>
            </div>
        `;
    }

    document.getElementById('recipeModalBody').innerHTML = html;
}

/**
 * Close recipe modal
 */
function closeRecipeModal() {
    const modal = document.getElementById('recipeModal');
    if (modal) {
        modal.style.display = 'none';
        // Clear modal body to prevent stale content
        document.getElementById('recipeModalBody').innerHTML = '';
    }
    loadRecipes(); // Refresh calendar
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
    const dateInput = document.getElementById('recipeDateInput');
    dateInput.value = dateString;
    dateInput.readOnly = true; // Make date readonly when clicked from calendar
    document.getElementById('recipeName').value = '';
    document.getElementById('ingredientsList').innerHTML = '';
    ingredientCounter = 0;
    
    addIngredientRow(); // Add one empty ingredient row
    document.getElementById('recipeFormModal').style.display = 'block';
}

/**
 * Close recipe form modal
 */
function closeRecipeFormModal() {
    const modal = document.getElementById('recipeFormModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('recipeForm').reset();
        document.getElementById('ingredientsList').innerHTML = '';
        ingredientCounter = 0;
        // Reset date input to editable
        const dateInput = document.getElementById('recipeDateInput');
        if (dateInput) {
            dateInput.readOnly = false;
        }
    }
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
        const response = await apiGet(`/recipes/${id}`);

        if (response.success) {
            const recipe = response.data;
            
            document.getElementById('recipeName').value = recipe.name;
            document.getElementById('recipeDate').value = recipe.date;
            const dateInput = document.getElementById('recipeDateInput');
            dateInput.value = recipe.date;
            dateInput.readOnly = true; // Make date readonly when editing existing recipe

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
                        const selected = ingredient.item_id === item.id ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.short_name} - ${item.name}</option>`;
                    });

                    html += `
                            </select>
                            <input type="number" class="ingredient-quantity" step="0.001" min="0" value="${ingredient.quantity}" placeholder="Quantity" required>
                            <button type="button" class="btn btn-danger btn-small" onclick="removeIngredientRow('${rowId}')">Remove</button>
                        </div>
                    `;

                    document.getElementById('ingredientsList').insertAdjacentHTML('beforeend', html);
                });
            } else {
                addIngredientRow();
            }
        }
    } catch (error) {
        console.error('Error loading recipe:', error);
        showError('Failed to load recipe');
    }
}

/**
 * Save recipe (create or update)
 */
async function saveRecipe(event) {
    event.preventDefault();

    const recipeId = document.getElementById('recipeId').value;
    const recipeName = document.getElementById('recipeName').value;
    const recipeDate = document.getElementById('recipeDateInput').value;

    // Collect ingredients
    const ingredients = [];
    document.querySelectorAll('.ingredient-item').forEach(item => {
        const itemId = item.querySelector('.ingredient-item-select').value;
        const quantity = item.querySelector('.ingredient-quantity').value;
        
        if (itemId && quantity) {
            ingredients.push({
                item_id: parseInt(itemId),
                quantity: parseFloat(quantity)
            });
        }
    });

    const recipeData = {
        name: recipeName,
        date: recipeDate,
        ingredients: ingredients
    };

    try {
        let response;
        if (recipeId) {
            // Update recipe
            response = await apiPut(`/recipes/${recipeId}`, recipeData);
        } else {
            // Create recipe
            response = await apiPost('/recipes', recipeData);
        }

        if (response.success) {
            showSuccess(response.message || 'Recipe saved successfully');
            closeRecipeFormModal();
            loadRecipes(); // Reload recipes
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
        const response = await apiDelete(`/recipes/${id}`);

        if (response.success) {
            showSuccess('Recipe deleted successfully');
            closeRecipeModal();
            loadRecipes(); // Reload recipes
        } else {
            showError('Failed to delete recipe');
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
    document.getElementById('importForm').reset();
    document.getElementById('fileList').innerHTML = '';
    document.getElementById('importProgress').classList.add('hidden');
    document.getElementById('importResults').classList.add('hidden');
}

/**
 * Close import modal
 */
function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}

/**
 * Handle file selection for import
 */
function handleFileSelection() {
    const files = document.getElementById('importFiles').files;
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';

    if (files.length > 0) {
        let html = '<h4>Selected Files:</h4><ul>';
        for (let i = 0; i < files.length; i++) {
            html += `<li>${files[i].name}</li>`;
        }
        html += '</ul>';
        fileList.innerHTML = html;
    }
}

/**
 * Import recipes from Excel files
 */
async function importRecipes(event) {
    event.preventDefault();

    const files = document.getElementById('importFiles').files;
    if (files.length === 0) {
        showError('Please select at least one file');
        return;
    }

    if (files.length > 7) {
        showError('Maximum 7 files allowed');
        return;
    }

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append(`files[${i}]`, files[i]);
    }

    document.getElementById('importProgress').classList.remove('hidden');
    document.getElementById('importResults').classList.add('hidden');

    try {
        const response = await apiUpload('/recipes/import', formData);

        document.getElementById('importProgress').classList.add('hidden');
        document.getElementById('importResults').classList.remove('hidden');

        if (response.success) {
            let resultsHtml = '<h4>Import Results:</h4>';
            resultsHtml += `<p>Processed: ${response.data.processed} files</p>`;
            
            if (response.data.recipes && response.data.recipes.length > 0) {
                resultsHtml += '<ul>';
                response.data.recipes.forEach(result => {
                    resultsHtml += `<li>${result.file}: ${result.status} - ${result.message || ''}</li>`;
                });
                resultsHtml += '</ul>';
            }
            
            if (response.data.failed_ingredients && response.data.failed_ingredients.length > 0) {
                resultsHtml += '<h5>Failed Ingredients:</h5><ul>';
                response.data.failed_ingredients.forEach(ing => {
                    resultsHtml += `<li>${ing.short_name}: ${ing.reason}</li>`;
                });
                resultsHtml += '</ul>';
            }
            
            document.getElementById('importResults').innerHTML = resultsHtml;
            showSuccess('Import completed');
            loadRecipes(); // Reload recipes
        } else {
            showError('Import failed');
        }
    } catch (error) {
        console.error('Error importing recipes:', error);
        document.getElementById('importProgress').classList.add('hidden');
        showError(error.message || 'Failed to import recipes');
    }
}

/**
 * Show success message
 */
function showSuccess(message) {
    alert(message); // You can replace this with a better notification system
}

/**
 * Show error message
 */
function showError(message) {
    alert(message); // You can replace this with a better notification system
}

// Update navigation functions
function previousMonth() {
    previousWeek();
}

function nextMonth() {
    nextWeek();
}
