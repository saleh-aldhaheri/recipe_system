<!-- Page Heading -->
<div class="mb-8">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl md:text-4xl font-black leading-tight tracking-tight text-text-primary">Transactions History</h1>
        <p class="text-text-secondary text-base">Audit log of all inventory movements and recipe usages</p>
    </div>
</div>

<!-- Search Bar -->
<div class="mb-6">
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="material-symbols-outlined text-text-secondary">search</span>
        </div>
        <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-3 border border-surface-border rounded-lg bg-white text-text-primary placeholder:text-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition-all shadow-sm" placeholder="Search by item, recipe, type, or operation..." onkeyup="handleSearch()">
    </div>
</div>

<!-- Data Table -->
<div class="rounded-xl border border-surface-border bg-white overflow-hidden shadow-sm flex flex-col">
    <div class="overflow-x-auto">
        <div id="transactionsTableContainer">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                <p class="mt-4 text-text-secondary">Loading transactions...</p>
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
