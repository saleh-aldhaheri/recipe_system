<!-- Welcome Section -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary/10 via-background-light to-primary/5 border border-surface-border p-8 md:p-12 mb-8 shadow-sm">
    <div class="relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl font-black text-text-primary mb-4">Recipe Management System</h1>
        <p class="text-lg text-text-secondary max-w-2xl mx-auto">Manage your recipes, items, and ingredients with ease. Professional inventory tracking for modern kitchens.</p>
    </div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/2"></div>
</div>

<!-- Features Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 group">
        <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
            <span class="material-symbols-outlined text-2xl">inventory_2</span>
        </div>
        <h3 class="text-lg font-bold text-text-primary mb-2">Manage Items</h3>
        <p class="text-text-secondary text-sm mb-4 leading-relaxed">Create, update, and manage your inventory items. Track balances and units for each item.</p>
        <a href="<?= url('items') ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark text-sm font-semibold transition-colors">
            <span>Go to Items</span>
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>

    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 group">
        <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
            <span class="material-symbols-outlined text-2xl">calendar_month</span>
        </div>
        <h3 class="text-lg font-bold text-text-primary mb-2">Recipes Calendar</h3>
        <p class="text-text-secondary text-sm mb-4 leading-relaxed">View recipes in a calendar format. Click on any day to manage recipes and import from Excel files.</p>
        <a href="<?= url('recipes') ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark text-sm font-semibold transition-colors">
            <span>Go to Calendar</span>
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>

    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 group">
        <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
            <span class="material-symbols-outlined text-2xl">local_offer</span>
        </div>
        <h3 class="text-lg font-bold text-text-primary mb-2">Manage Ingredients</h3>
        <p class="text-text-secondary text-sm mb-4 leading-relaxed">Create and manage recipe ingredients. Automatically track item balances when ingredients are used.</p>
        <a href="<?= url('ingredients') ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark text-sm font-semibold transition-colors">
            <span>Go to Ingredients</span>
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>

    <div class="bg-white border border-surface-border rounded-xl p-6 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 group">
        <div class="size-12 flex items-center justify-center rounded-lg bg-primary/10 text-primary mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
            <span class="material-symbols-outlined text-2xl">receipt_long</span>
        </div>
        <h3 class="text-lg font-bold text-text-primary mb-2">View Transactions</h3>
        <p class="text-text-secondary text-sm mb-4 leading-relaxed">Track operations on your stock at any time frame with detailed transaction history.</p>
        <a href="<?= url('transactions') ?>" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark text-sm font-semibold transition-colors">
            <span>Go to Transactions</span>
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>
</div>

<!-- How It Works Section -->
<div class="bg-white border border-surface-border rounded-xl p-6 md:p-8 shadow-sm">
    <h2 class="text-2xl font-bold text-text-primary mb-6">How It Works</h2>
    <div class="space-y-4">
        <div class="flex gap-4">
            <div class="flex-shrink-0 size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">1</div>
            <div>
                <p class="text-text-primary font-semibold mb-1">Manage Items</p>
                <p class="text-text-secondary text-sm">First, add your inventory items with their balances and units.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex-shrink-0 size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">2</div>
            <div>
                <p class="text-text-primary font-semibold mb-1">Create Recipes</p>
                <p class="text-text-secondary text-sm">Use the calendar to create recipes for specific dates. Add ingredients to each recipe.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex-shrink-0 size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">3</div>
            <div>
                <p class="text-text-primary font-semibold mb-1">Track Usage</p>
                <p class="text-text-secondary text-sm">When you create or update ingredients, the system automatically deducts quantities from item balances.</p>
            </div>
        </div>
        <div class="flex gap-4">
            <div class="flex-shrink-0 size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">4</div>
            <div>
                <p class="text-text-primary font-semibold mb-1">Import Recipes</p>
                <p class="text-text-secondary text-sm">Import multiple Excel files at once to quickly add recipes and ingredients.</p>
            </div>
        </div>
    </div>
</div>
