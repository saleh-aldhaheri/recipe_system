<!-- Page Header -->
<div class="page-header">
    <h1>Recipes Calendar</h1>
    <div>
        <button class="btn btn-success" onclick="openImportModal()">Import Recipes</button>
    </div>
</div>

<!-- Calendar Navigation -->
<div class="calendar-nav">
    <button class="btn btn-secondary" onclick="previousMonth()">← Previous Month</button>
    <h2 id="currentMonthYear"></h2>
    <button class="btn btn-secondary" onclick="nextMonth()">Next Month →</button>
</div>

<!-- Calendar -->
<div class="card">
    <div class="calendar-header">
        <div>Sun</div>
        <div>Mon</div>
        <div>Tue</div>
        <div>Wed</div>
        <div>Thu</div>
        <div>Fri</div>
        <div>Sat</div>
    </div>
    <div class="calendar" id="calendar"></div>
</div>

<!-- Recipe Modal (for viewing/editing recipes for a day) -->
<div id="recipeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="recipeModalTitle">Recipes for <span id="selectedDate"></span></h2>
            <button class="close" onclick="closeRecipeModal()">&times;</button>
        </div>
        <div id="recipeModalBody">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading recipes...</p>
            </div>
        </div>
    </div>
</div>

<!-- Recipe Form Modal (for creating/editing a recipe) -->
<div id="recipeFormModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="recipeFormTitle">Add Recipe</h2>
            <button class="close" onclick="closeRecipeFormModal()">&times;</button>
        </div>
        <form id="recipeForm" onsubmit="saveRecipe(event)">
            <input type="hidden" id="recipeId">
            <input type="hidden" id="recipeDate">
            
            <div class="form-group">
                <label for="recipeName">Recipe Name *</label>
                <input type="text" id="recipeName" required minlength="3" maxlength="255">
            </div>

            <div class="form-group">
                <label for="recipeDateInput">Date *</label>
                <input type="date" id="recipeDateInput" required>
            </div>

            <!-- Ingredients Section -->
            <div class="form-group">
                <label>Ingredients</label>
                <button type="button" class="btn btn-success btn-small" onclick="addIngredientRow()">+ Add Ingredient</button>
                <div id="ingredientsList" class="ingredient-list"></div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeRecipeFormModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Recipe</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Import Recipes from Excel</h2>
            <button class="close" onclick="closeImportModal()">&times;</button>
        </div>
        <form id="importForm" onsubmit="importRecipes(event)">
            <div class="form-group">
                <label>Select Excel Files (Max 7 files)</label>
                <input type="file" id="importFiles" accept=".xlsx,.xls" multiple required>
                <small style="color: #7f8c8d; display: block; margin-top: 0.5rem;">
                    Select one or more Excel files (.xlsx, .xls). Maximum 7 files allowed.
                </small>
            </div>

            <div id="fileList" class="file-list"></div>

            <div id="importProgress" class="hidden" style="margin: 1rem 0;">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Importing files...</p>
                </div>
            </div>

            <div id="importResults" class="hidden" style="margin: 1rem 0;"></div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Close</button>
                <button type="submit" class="btn btn-primary">Import Files</button>
            </div>
        </form>
    </div>
</div>

