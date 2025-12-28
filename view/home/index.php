<!-- Welcome Section -->
<div class="welcome-section">
    <h1>Recipe Management System</h1>
    <p>Manage your recipes, items, and ingredients with ease</p>
</div>

<!-- Features -->
<div class="features">
    <div class="feature-card">
        <h3>📦 Manage Items</h3>
        <p>Create, update, and manage your inventory items. Track balances and units for each item.</p>
        <a href="<?= url('items') ?>" class="btn btn-primary">Go to Items</a>
    </div>

    <div class="feature-card">
        <h3>📅 Recipes Calendar</h3>
        <p>View recipes in a calendar format. Click on any day to manage recipes and import from Excel files.</p>
        <a href="<?= url('recipes') ?>" class="btn btn-primary">Go to Calendar</a>
    </div>

    <div class="feature-card">
        <h3>🥘 Manage Ingredients</h3>
        <p>Create and manage recipe ingredients. Automatically track item balances when ingredients are used.</p>
        <a href="<?= url('ingredients') ?>" class="btn btn-primary">Go to Ingredients</a>
    </div>
</div>

<!-- Quick Info -->
<div class="card" style="margin-top: 3rem;">
    <h2 style="color: #2c3e50; margin-bottom: 1rem;">How It Works</h2>
    <div style="line-height: 2;">
        <p><strong>1. Manage Items:</strong> First, add your inventory items with their balances and units.</p>
        <p><strong>2. Create Recipes:</strong> Use the calendar to create recipes for specific dates. Add ingredients to each recipe.</p>
        <p><strong>3. Track Usage:</strong> When you create or update ingredients, the system automatically deducts quantities from item balances.</p>
        <p><strong>4. Import Recipes:</strong> Import multiple Excel files at once to quickly add recipes and ingredients.</p>
    </div>
</div>

<style>
.welcome-section {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    margin-bottom: 3rem;
}
.welcome-section h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.welcome-section p {
    font-size: 1.2rem;
    opacity: 0.9;
}
.features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}
.feature-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}
.feature-card:hover {
    transform: translateY(-5px);
}
.feature-card h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}
.feature-card p {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
}
</style>
