/**
 * Recipes Calendar Page - Weekly View
 * This file handles weekly calendar display and recipe CRUD operations using AJAX
 */

// Current week start date (Sunday)
let currentWeekStart = getSunday(new Date());
let selectedDay = null;
let allRecipes = []; // Cache all recipes
let ingredientCounter = 0; // Counter for ingredient rows

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
    loadRecipes();
    
    // Use event delegation for modal closing and button clicks
    document.addEventListener('click', function(event) {
        const target = event.target;
        
        // Close modal when clicking outside
        const recipeModal = document.getElementById('recipeModal');
        if (recipeModal && recipeModal.style.display === 'block') {
            if (target === recipeModal) {
                closeRecipeModal();
                return;
            }
        }
        
        const recipeFormModal = document.getElementById('recipeFormModal');
        if (recipeFormModal && recipeFormModal.style.display === 'block') {
            if (target === recipeFormModal) {
                closeRecipeFormModal();
                return;
            }
        }
        
        // Handle button clicks inside modals using event delegation
        if (target.closest('#recipeModalBody')) {
            const action = target.getAttribute('data-action');
            
            // Handle "Add New Recipe" button
            if (action === 'add-recipe') {
                const dateString = selectedDay ? selectedDay.toISOString().split('T')[0] : '';
                if (dateString) {
                    event.preventDefault();
                    event.stopPropagation();
                    openRecipeForm(dateString);
                }
                return;
            }
            
            // Handle "Edit" button
            if (action === 'edit-recipe') {
                const recipeId = target.getAttribute('data-recipe-id');
                if (recipeId) {
                    event.preventDefault();
                    event.stopPropagation();
                    editRecipe(parseInt(recipeId));
                }
                return;
            }
            
            // Handle "Delete" button
            if (action === 'delete-recipe') {
                const recipeId = target.getAttribute('data-recipe-id');
                if (recipeId) {
                    event.preventDefault();
                    event.stopPropagation();
                    deleteRecipe(parseInt(recipeId));
                }
                return;
            }
        }
        
        // Handle close button clicks
        if (target.classList.contains('close') || (target.closest('.close') && !target.closest('button:not(.close)'))) {
            event.preventDefault();
            event.stopPropagation();
            const modal = target.closest('.modal');
            if (modal) {
                if (modal.id === 'recipeModal') {
                    closeRecipeModal();
                } else if (modal.id === 'recipeFormModal') {
                    closeRecipeFormModal();
                } else if (modal.id === 'importModal') {
                    closeImportModal();
                }
            }
            return;
        }
        
        // Prevent modal content clicks from closing modal (but allow button clicks)
        if (target.closest('.modal-content') && !target.closest('button') && !target.closest('a')) {
            event.stopPropagation();
        }
    });
});

/**
 * Get Sunday of the week for a given date
 */
function getSunday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day; // Get Sunday of the week
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
    const startYear = weekStart.getFullYear();
    const endYear = weekEnd.getFullYear();
    
    let weekText;
    if (startYear !== endYear) {
        // Different years: show year for both dates
        weekText = `${startMonth} ${weekStart.getDate()}, ${startYear} - ${endMonth} ${weekEnd.getDate()}, ${endYear}`;
    } else if (weekStart.getMonth() !== weekEnd.getMonth()) {
        // Same year, different months: show year at the end
        weekText = `${startMonth} ${weekStart.getDate()} - ${endMonth} ${weekEnd.getDate()}, ${startYear}`;
    } else {
        // Same month and year: show year at the end
        weekText = `${startMonth} ${weekStart.getDate()} - ${weekEnd.getDate()}, ${startYear}`;
    }
    
    document.getElementById('currentMonthYear').textContent = weekText;

    // Clear calendar
    const calendar = document.getElementById('calendar');
    if (!calendar) {
        console.error('Calendar element not found');
        return;
    }
    calendar.innerHTML = '';

    // Add days of the week (Sunday to Saturday)
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    for (let i = 0; i < 7; i++) {
        const dayDate = new Date(weekStart);
        dayDate.setDate(dayDate.getDate() + i);
        const dateString = dayDate.toISOString().split('T')[0];
        
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        // Add today class
        const today = new Date();
        if (dayDate.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        // Click handler - use event delegation approach
        dayElement.setAttribute('data-date', dateString);
        dayElement.style.cursor = 'pointer';
        dayElement.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const clickedDate = new Date(dayDate);
            openDayRecipes(clickedDate);
        });

        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.innerHTML = `
            <div class="day-name">${dayNames[i]}</div>
            <div class="day-number">${dayDate.getDate()}</div>
        `;
        dayElement.appendChild(dayHeader);

        const recipesContainer = document.createElement('div');
        recipesContainer.className = 'calendar-day-recipes';
        recipesContainer.id = `recipes-${dateString}`;
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
    if (!dayDate) {
        console.error('Day date is required');
        return;
    }
    
    // Close any other open modals first
    const recipeFormModal = document.getElementById('recipeFormModal');
    if (recipeFormModal && recipeFormModal.style.display === 'block') {
        closeRecipeFormModal();
    }
    
    selectedDay = new Date(dayDate);
    const dateString = selectedDay.toISOString().split('T')[0];
    
    const modal = document.getElementById('recipeModal');
    const selectedDateEl = document.getElementById('selectedDate');
    const modalTitleEl = document.getElementById('recipeModalTitle');
    const modalBodyEl = document.getElementById('recipeModalBody');
    
    if (!modal || !selectedDateEl || !modalTitleEl || !modalBodyEl) {
        console.error('Modal elements not found');
        return;
    }
    
    // Close modal if already open to reset it
    if (modal.style.display === 'block') {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        void modal.offsetHeight;
    }
    
    // Set content
    selectedDateEl.textContent = dateString;
    modalTitleEl.textContent = `Recipes for ${dateString}`;
    modalBodyEl.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading recipes...</p>
        </div>
    `;
    
    // Force reflow and show modal
    void modal.offsetHeight;
    modal.style.visibility = 'visible';
    modal.style.display = 'block';
    modal.style.opacity = '1';

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
    let html = '';

    if (recipes.length === 0) {
        // No recipe exists for this day - show option to create
        html = `
            <div style="margin-bottom: 1rem;">
                <button class="btn btn-primary" data-action="add-recipe">+ Add New Recipe</button>
            </div>
            <p>No recipes for this day. Click "Add New Recipe" to create one.</p>
        `;
    } else {
        // Recipe exists for this day - show recipe details with Edit/Delete options only
        const recipe = recipes[0];
        html = `
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
                    <button class="btn btn-primary btn-small" data-recipe-id="${recipe.id}" data-action="edit-recipe">Edit</button>
                    <button class="btn btn-danger btn-small" data-recipe-id="${recipe.id}" data-action="delete-recipe">Delete</button>
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
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        const modalBody = document.getElementById('recipeModalBody');
        if (modalBody) {
            modalBody.innerHTML = '';
        }
        selectedDay = null;
        
        // Force reflow
        void modal.offsetHeight;
    }
}

/**
 * Open recipe form for creating new recipe
 * @param {string} dateString
 */
function openRecipeForm(dateString) {
    closeRecipeModal();
    
    const formModal = document.getElementById('recipeFormModal');
    if (!formModal) {
        console.error('Recipe form modal not found');
        return;
    }
    
    document.getElementById('recipeFormTitle').textContent = 'Add New Recipe';
    document.getElementById('recipeId').value = '';
    document.getElementById('recipeDate').value = dateString;
    const dateInput = document.getElementById('recipeDateInput');
    dateInput.value = dateString;
    dateInput.readOnly = true;
    document.getElementById('recipeName').value = '';
    document.getElementById('ingredientsList').innerHTML = '';
    ingredientCounter = 0;
    
    addIngredientRow();
    
    formModal.style.display = 'none';
    formModal.style.visibility = 'hidden';
    void formModal.offsetHeight;
    formModal.style.visibility = 'visible';
    formModal.style.display = 'block';
}

/**
 * Close recipe form modal
 */
function closeRecipeFormModal() {
    const modal = document.getElementById('recipeFormModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        document.getElementById('recipeForm').reset();
        document.getElementById('ingredientsList').innerHTML = '';
        ingredientCounter = 0;
        const dateInput = document.getElementById('recipeDateInput');
        if (dateInput) {
            dateInput.readOnly = false;
        }
        void modal.offsetHeight;
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
        const formModal = document.getElementById('recipeFormModal');
        if (formModal) {
            formModal.style.display = 'none';
            formModal.style.visibility = 'hidden';
            void formModal.offsetHeight;
            formModal.style.visibility = 'visible';
            formModal.style.display = 'block';
        }
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
    const confirmed = await confirmAction('Are you sure you want to delete this recipe? This action cannot be undone.', 'Delete Recipe');
    if (!confirmed) {
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
    const modal = document.getElementById('importModal');
    if (modal) {
        document.getElementById('importForm').reset();
        document.getElementById('fileList').innerHTML = '';
        document.getElementById('importProgress').classList.add('hidden');
        document.getElementById('importResults').classList.add('hidden');
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        void modal.offsetHeight;
        modal.style.visibility = 'visible';
        modal.style.display = 'block';
    }
}

/**
 * Close import modal
 */
function closeImportModal() {
    const modal = document.getElementById('importModal');
    if (modal) {
        modal.style.display = 'none';
        modal.style.visibility = 'hidden';
        void modal.offsetHeight;
    }
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
                resultsHtml += '<h5>Failed Ingredients (' + response.data.failed_ingredients.length + '):</h5>';
                resultsHtml += '<button class="btn btn-primary btn-small" onclick="downloadFailedIngredientsCSV()" style="margin-bottom: 1rem;">Download Failed Ingredients as CSV</button>';
                resultsHtml += '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
                resultsHtml += '<thead><tr><th style="border: 1px solid #ddd; padding: 0.5rem;">Short Name</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Name</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Quantity</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Available Balance</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Batch Number</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Recipe</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Date</th><th style="border: 1px solid #ddd; padding: 0.5rem;">Reason</th></tr></thead><tbody>';
                response.data.failed_ingredients.forEach(ing => {
                    resultsHtml += `<tr>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.short_name || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.name || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.quantity || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.available_balance !== undefined ? ing.available_balance : 'N/A'}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.batch_number || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.recipe_name || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.recipe_date || ''}</td>
                        <td style="border: 1px solid #ddd; padding: 0.5rem;">${ing.reason || ''}</td>
                    </tr>`;
                });
                resultsHtml += '</tbody></table>';
                
                window.failedIngredientsData = response.data.failed_ingredients;
            } else {
                window.failedIngredientsData = [];
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
    if (typeof notifications !== 'undefined') {
        notifications.success(message);
    } else {
        console.log('Success:', message);
    }
}

/**
 * Show error message
 */
function showError(message) {
    if (typeof notifications !== 'undefined') {
        notifications.error(message);
    } else {
        console.error('Error:', message);
    }
}

/**
 * Download failed ingredients as CSV
 */
function downloadFailedIngredientsCSV() {
    if (!window.failedIngredientsData || window.failedIngredientsData.length === 0) {
        showError('No failed ingredients to download');
        return;
    }

    const headers = ['Short Name', 'Name', 'Quantity', 'Available Balance', 'Batch Number', 'Recipe Name', 'Recipe Date', 'Reason'];
    const rows = window.failedIngredientsData.map(ing => [
        ing.short_name || '',
        ing.name || '',
        ing.quantity || '',
        ing.available_balance !== undefined ? ing.available_balance : '',
        ing.batch_number || '',
        ing.recipe_name || '',
        ing.recipe_date || '',
        ing.reason || ''
    ]);

    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => {
            const cellStr = String(cell);
            if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                return '"' + cellStr.replace(/"/g, '""') + '"';
            }
            return cellStr;
        }).join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'failed_ingredients_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Update navigation functions
function previousMonth() {
    previousWeek();
}

function nextMonth() {
    nextWeek();
}
