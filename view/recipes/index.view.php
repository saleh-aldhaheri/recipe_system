<!-- Breadcrumbs -->
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="<?= url('dashboard') ?>" class="text-text-secondary hover:text-primary transition-colors font-medium">Dashboard</a>
    <span class="material-symbols-outlined text-xs text-text-secondary">chevron_right</span>
    <span class="text-text-primary font-medium">Recipes Calendar</span>
</div>

<!-- Page Heading & Actions -->
<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 mb-8">
    <div>
        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight text-text-primary mb-2">Recipes Calendar</h1>
        <p class="text-text-secondary font-medium">Manage your weekly meal schedule efficiently.</p>
    </div>
    <div class="flex flex-wrap items-center gap-4">
        <!-- Week Picker -->
        <div class="flex items-center bg-white rounded-lg p-1 border border-surface-border shadow-sm">
            <button onclick="previousWeek()" class="p-2 hover:bg-background-light hover:text-primary rounded-md text-text-secondary transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <div class="flex items-center gap-2 px-4 border-x border-surface-border/30">
                <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>
                <span class="text-sm font-bold text-text-primary" id="currentMonthYear">Loading...</span>
            </div>
            <button onclick="nextWeek()" class="p-2 hover:bg-background-light hover:text-primary rounded-md text-text-secondary transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        </div>
        <!-- Import Action -->
        <button onclick="openImportModal()" class="flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-5 py-2.5 rounded-lg font-bold shadow-md transition-all active:scale-95">
            <span class="material-symbols-outlined text-[20px]">download</span>
            <span>Import Recipes</span>
        </button>
    </div>
</div>

<!-- Calendar -->
<div class="bg-white rounded-xl border border-surface-border overflow-hidden shadow-sm">
    <div class="grid grid-cols-7 border-b border-surface-border bg-background-light">
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Sun</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Mon</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Tue</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Wed</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Thu</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider border-r border-surface-border">Fri</div>
        <div class="p-3 text-center text-xs font-semibold text-text-secondary uppercase tracking-wider">Sat</div>
    </div>
    <div class="grid grid-cols-7" id="calendar">
        <!-- Calendar days will be inserted here by JavaScript -->
    </div>
</div>

<!-- Recipe Modal (for viewing/editing recipes for a day) -->
<div id="recipeModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeRecipeModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-border">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 id="recipeModalTitle" class="text-xl font-bold text-text-primary">Recipes for <span id="selectedDate"></span></h2>
                <button onclick="closeRecipeModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="recipeModalBody" class="px-6 py-6">
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                    <p class="mt-4 text-text-secondary">Loading recipes...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipe Form Modal (for creating/editing a recipe) -->
<div id="recipeFormModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeRecipeFormModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-surface-border max-h-[90vh] overflow-y-auto">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 id="recipeFormTitle" class="text-xl font-bold text-text-primary">Add Recipe</h2>
                <button onclick="closeRecipeFormModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="recipeForm" onsubmit="saveRecipe(event)" class="px-6 py-6">
                <input type="hidden" id="recipeId">
                <input type="hidden" id="recipeDate">
                
                <div class="mb-5">
                    <label for="recipeName" class="block text-sm font-medium text-text-secondary mb-2">Recipe Name *</label>
                    <input type="text" id="recipeName" required minlength="3" maxlength="255" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                </div>

                <div class="mb-5">
                    <label for="recipeDateInput" class="block text-sm font-medium text-text-secondary mb-2">Date *</label>
                    <input type="date" id="recipeDateInput" required class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                    <p class="mt-1 text-xs text-text-secondary">Date is locked when selected from calendar</p>
                </div>

                <!-- Ingredients Section -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-text-secondary">Ingredients</label>
                        <button type="button" onclick="addIngredientRow()" class="px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 text-xs font-medium transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">add</span>
                            <span>Add Ingredient</span>
                        </button>
                    </div>
                    <div id="ingredientsList" class="space-y-3"></div>
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-surface-border">
                    <button type="button" onclick="closeRecipeFormModal()" class="px-5 py-2.5 rounded-lg border border-surface-border bg-white text-text-secondary hover:bg-background-light hover:text-text-primary transition-all text-sm font-medium shadow-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white hover:bg-primary-dark transition-all text-sm font-bold shadow-md">
                        Save Recipe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-surface-border">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 class="text-xl font-bold text-text-primary">Import Recipes from Excel</h2>
                <button onclick="closeImportModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="importForm" onsubmit="importRecipes(event)" class="px-6 py-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-text-secondary mb-2">Select Excel Files (Max 7 files)</label>
                    <input type="file" id="importFiles" accept=".xlsx,.xls" multiple required class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary-dark transition-all shadow-sm">
                    <p class="mt-2 text-xs text-text-secondary">Select one or more Excel files (.xlsx, .xls). Maximum 7 files allowed.</p>
                </div>

                <div id="fileList" class="mb-5 space-y-2"></div>

                <div id="importProgress" class="hidden mb-5">
                    <div class="flex flex-col items-center justify-center py-8">
                        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                        <p class="mt-4 text-text-secondary">Importing files...</p>
                    </div>
                </div>

                <div id="importResults" class="hidden mb-5"></div>

                <div class="flex gap-3 justify-end pt-4 border-t border-surface-border">
                    <button type="button" onclick="closeImportModal()" class="px-5 py-2.5 rounded-lg border border-surface-border bg-white text-text-secondary hover:bg-background-light hover:text-text-primary transition-all text-sm font-medium shadow-sm">
                        Close
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white hover:bg-primary-dark transition-all text-sm font-bold shadow-md">
                        Import Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
