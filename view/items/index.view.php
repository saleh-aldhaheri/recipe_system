<!-- Breadcrumbs -->
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="<?= url('dashboard') ?>" class="text-text-secondary hover:text-primary transition-colors font-medium">Dashboard</a>
    <span class="material-symbols-outlined text-xs text-text-secondary">chevron_right</span>
    <span class="text-text-primary font-medium">Items Inventory</span>
</div>

<!-- Page Heading & Actions -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
    <div class="flex flex-col gap-2 max-w-2xl">
        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight text-text-primary">Items Management</h1>
        <p class="text-text-secondary text-base">Manage your inventory items, check balances, and update details efficiently.</p>
    </div>
    <button onclick="openItemModal()" class="flex items-center gap-2 h-11 px-5 bg-primary hover:bg-primary-dark text-white text-sm font-bold rounded-lg transition-all shadow-md transform active:scale-95 whitespace-nowrap">
        <span class="material-symbols-outlined text-[20px]">add</span>
        <span>Add New Item</span>
    </button>
</div>

<!-- Filters & Search -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
    <div class="md:col-span-8 lg:col-span-9 relative group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="material-symbols-outlined text-text-secondary group-focus-within:text-primary transition-colors">search</span>
        </div>
        <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-3 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all shadow-sm" placeholder="Search items by name, code, or SKU..." onkeyup="handleSearch()">
    </div>
    <div class="md:col-span-4 lg:col-span-3">
        <div class="relative">
            <select id="statusFilter" onchange="handleStatusFilter()" class="block w-full pl-3 pr-10 py-3 text-base border border-surface-border focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm rounded-lg bg-white text-text-primary appearance-none cursor-pointer shadow-sm [&>option]:bg-white [&>option]:text-text-primary">
                <option value="all">Status: All</option>
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-text-secondary">
                <span class="material-symbols-outlined">expand_more</span>
            </div>
        </div>
    </div>
</div>

<!-- Table Container -->
<div class="bg-white rounded-xl border border-surface-border overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <div id="itemsTableContainer">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading items...</p>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="border-t border-surface-border px-6 py-4 bg-background-light">
        <div id="pagination" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Pagination will be inserted here by JavaScript -->
        </div>
    </div>
</div>

<!-- Item Modal (for Create/Edit) -->
<div id="itemModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeItemModal()"></div>
        
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-surface-border">
            <div class="bg-white px-6 py-5 border-b border-surface-border flex items-center justify-between">
                <h2 id="modalTitle" class="text-xl font-bold text-text-primary">Add New Item</h2>
                <button onclick="closeItemModal()" class="text-text-secondary hover:text-text-primary transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="itemForm" onsubmit="saveItem(event)" class="px-6 py-6">
                <input type="hidden" id="itemId">
                
                <div class="mb-5">
                    <label for="itemName" class="block text-sm font-medium text-text-secondary mb-2">Name *</label>
                    <input type="text" id="itemName" required minlength="3" maxlength="255" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                </div>

                <div class="mb-5">
                    <label for="itemShortName" class="block text-sm font-medium text-text-secondary mb-2">Short Name *</label>
                    <input type="text" id="itemShortName" required maxlength="50" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="itemBalance" class="block text-sm font-medium text-text-secondary mb-2">Balance *</label>
                        <input type="number" id="itemBalance" step="0.001" min="0" required class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                    </div>

                    <div>
                        <label for="itemUnit" class="block text-sm font-medium text-text-secondary mb-2">Unit *</label>
                        <input type="text" id="itemUnit" required maxlength="20" class="w-full px-4 py-2.5 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm">
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-4">
                    <button type="button" onclick="closeItemModal()" class="px-5 py-2.5 rounded-lg border border-surface-border bg-white text-text-secondary hover:bg-background-light hover:text-text-primary transition-all text-sm font-medium shadow-sm">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white hover:bg-primary-dark transition-all text-sm font-bold shadow-md">
                        Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
