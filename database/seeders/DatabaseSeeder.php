<?php

// Define constants before loading bootstrap
define('BASE', __DIR__.'/../../');
define('APP', BASE.'app'.DIRECTORY_SEPARATOR);
define('Core', BASE.'core'.DIRECTORY_SEPARATOR);

require_once BASE.'vendor/autoload.php';
require_once __DIR__.'/../bootstrap-seeder.php';

use App\Models\Ingredient;
use App\Models\Item;
use App\Models\Recipe;
use Carbon\Carbon;

class DatabaseSeeder
{
    private array $items = [];

    private array $recipes = [];

    private bool $clearExisting = false;

    public function __construct(bool $clearExisting = false)
    {
        $this->clearExisting = $clearExisting;
    }

    public function run(): void
    {
        echo "🌱 Starting database seeding...\n\n";

        if ($this->clearExisting) {
            echo "🗑️  Clearing existing data...\n";
            $this->clearData();
            echo "   ✅ Data cleared\n\n";
        }

        $this->seedItems();
        $this->seedRecipes();
        $this->seedIngredients();

        echo "\n✅ Database seeding completed!\n";
        echo "📊 Summary:\n";
        echo '   - Items: '.count($this->items)."\n";
        echo '   - Recipes: '.count($this->recipes)."\n";
        echo '   - Total Ingredients: '.Ingredient::count()."\n";
        echo '   - Total Transactions: '.\App\Models\Transaction::count()."\n";
    }

    private function clearData(): void
    {
        // Delete in order to respect foreign key constraints
        \App\Models\Transaction::query()->delete();
        Ingredient::query()->delete();
        Recipe::query()->delete();
        Item::query()->delete();

        // Reset auto increment using Capsule (already set up in bootstrap)
        $capsule = \Illuminate\Database\Capsule\Manager::connection();
        $capsule->statement('ALTER TABLE transactions AUTO_INCREMENT = 1');
        $capsule->statement('ALTER TABLE ingredients AUTO_INCREMENT = 1');
        $capsule->statement('ALTER TABLE recipes AUTO_INCREMENT = 1');
        $capsule->statement('ALTER TABLE items AUTO_INCREMENT = 1');
    }

    private function seedItems(): void
    {
        echo "📦 Seeding Items...\n";

        $itemsData = [
            ['name' => 'Chicken Breast', 'short_name' => 'CH-B', 'unit' => 'KG', 'balance' => 5000.000],
            ['name' => 'Chicken Thigh', 'short_name' => 'CH-T', 'unit' => 'KG', 'balance' => 3000.000],
            ['name' => 'Beef Mince', 'short_name' => 'BF-M', 'unit' => 'KG', 'balance' => 4000.000],
            ['name' => 'Beef Steak', 'short_name' => 'BF-S', 'unit' => 'KG', 'balance' => 2500.000],
            ['name' => 'Pork Shoulder', 'short_name' => 'PK-S', 'unit' => 'KG', 'balance' => 3500.000],
            ['name' => 'Pork Belly', 'short_name' => 'PK-B', 'unit' => 'KG', 'balance' => 2800.000],
            ['name' => 'Fish Fillet', 'short_name' => 'FS-F', 'unit' => 'KG', 'balance' => 2000.000],
            ['name' => 'Shrimp', 'short_name' => 'SH', 'unit' => 'KG', 'balance' => 1500.000],
            ['name' => 'Onion', 'short_name' => 'ON', 'unit' => 'KG', 'balance' => 2000.000],
            ['name' => 'Garlic', 'short_name' => 'GL', 'unit' => 'KG', 'balance' => 1000.000],
            ['name' => 'Ginger', 'short_name' => 'GN', 'unit' => 'KG', 'balance' => 800.000],
            ['name' => 'Tomato', 'short_name' => 'TM-V', 'unit' => 'KG', 'balance' => 1500.000],
            ['name' => 'Bell Pepper', 'short_name' => 'BP-P', 'unit' => 'KG', 'balance' => 1200.000],
            ['name' => 'Carrot', 'short_name' => 'CR', 'unit' => 'KG', 'balance' => 1400.000],
            ['name' => 'Potato', 'short_name' => 'PT', 'unit' => 'KG', 'balance' => 2000.000],
            ['name' => 'Rice', 'short_name' => 'RC', 'unit' => 'KG', 'balance' => 5000.000],
            ['name' => 'Flour', 'short_name' => 'FL', 'unit' => 'KG', 'balance' => 3000.000],
            ['name' => 'Sugar', 'short_name' => 'SG', 'unit' => 'KG', 'balance' => 2500.000],
            ['name' => 'Salt', 'short_name' => 'ST', 'unit' => 'KG', 'balance' => 2000.000],
            ['name' => 'Black Pepper', 'short_name' => 'BP-S', 'unit' => 'KG', 'balance' => 500.000],
            ['name' => 'Curry Powder', 'short_name' => 'CP', 'unit' => 'KG', 'balance' => 800.000],
            ['name' => 'Turmeric', 'short_name' => 'TM-S', 'unit' => 'KG', 'balance' => 400.000],
            ['name' => 'Cumin', 'short_name' => 'CM-S', 'unit' => 'KG', 'balance' => 300.000],
            ['name' => 'Coriander', 'short_name' => 'CN', 'unit' => 'KG', 'balance' => 350.000],
            ['name' => 'Coconut Milk', 'short_name' => 'CM-L', 'unit' => 'L', 'balance' => 2000.000],
            ['name' => 'Olive Oil', 'short_name' => 'OO', 'unit' => 'L', 'balance' => 1000.000],
            ['name' => 'Soy Sauce', 'short_name' => 'SS', 'unit' => 'L', 'balance' => 600.000],
            ['name' => 'Fish Sauce', 'short_name' => 'FS', 'unit' => 'L', 'balance' => 500.000],
            ['name' => 'Chili Paste', 'short_name' => 'CH-P', 'unit' => 'KG', 'balance' => 200.000],
            ['name' => 'Lemongrass', 'short_name' => 'LG', 'unit' => 'KG', 'balance' => 100.000],
        ];

        foreach ($itemsData as $itemData) {
            $item = Item::create($itemData);
            $this->items[] = $item;
            echo "   ✓ Created: {$item->name} ({$item->short_name})\n";
        }

        echo '   ✅ Created '.count($this->items)." items\n\n";
    }

    private function seedRecipes(): void
    {
        echo "🍳 Seeding Recipes...\n";

        $recipeNames = [
            'Chicken Curry',
            'Beef Stew',
            'Pork Adobo',
            'Fish Curry',
            'Shrimp Scampi',
            'Vegetable Stir Fry',
            'Tomato Pasta',
            'Beef Rendang',
            'Chicken Satay',
            'Pork Sinigang',
            'Fish Sinigang',
            'Chicken Adobo',
            'Beef Bulgogi',
            'Pork Belly Roast',
            'Chicken Teriyaki',
            'Beef Curry',
            'Pork Curry',
            'Fish Teriyaki',
            'Chicken Korma',
            'Beef Korma',
        ];

        // Create recipes for the last year (365 days)
        $startDate = Carbon::now()->subYear();
        $recipeIndex = 0;

        for ($day = 0; $day < 365; $day++) {
            $date = $startDate->copy()->addDays($day);

            // Create 1-3 recipes per day randomly
            $recipesPerDay = rand(1, 3);

            for ($i = 0; $i < $recipesPerDay; $i++) {
                $recipeName = $recipeNames[$recipeIndex % count($recipeNames)];
                $recipeNameWithDate = $recipeName.' - '.$date->format('M d');

                $recipe = Recipe::create([
                    'name' => $recipeNameWithDate,
                    'date' => $date->format('Y-m-d'),
                ]);

                $this->recipes[] = $recipe;
                $recipeIndex++;
            }
        }

        echo '   ✅ Created '.count($this->recipes)." recipes\n\n";
    }

    private function seedIngredients(): void
    {
        echo "🥘 Seeding Ingredients...\n";

        $ingredientCount = 0;
        $recipeIndex = 0;
        $errors = 0;

        foreach ($this->recipes as $recipe) {
            // Each recipe has 3-8 ingredients
            $numIngredients = rand(3, 8);

            // Randomly select items for this recipe
            $selectedItems = $this->getRandomItems($numIngredients);

            // Set created_at for transactions to match recipe date
            $recipeDate = Carbon::parse($recipe->date);

            foreach ($selectedItems as $item) {
                // Random quantity between 0.5 and 10.0
                $quantity = round(rand(50, 1000) / 100, 3);

                // Check if item has enough balance
                $item->refresh();
                if ($item->balance < $quantity) {
                    // Skip this ingredient if not enough balance
                    $errors++;

                    continue;
                }

                try {
                    // Temporarily set Carbon::now() to recipe date for transaction creation
                    Carbon::setTestNow($recipeDate);

                    // Create ingredient (this will trigger transaction creation via boot events)
                    $ingredient = Ingredient::create([
                        'recipe_id' => $recipe->id,
                        'item_id' => $item->id,
                        'quantity' => $quantity,
                    ]);

                    // Reset Carbon::now() to current time
                    Carbon::setTestNow();

                    $ingredientCount++;
                } catch (\Exception $e) {
                    // Reset Carbon::now() in case of error
                    Carbon::setTestNow();
                    // Skip if there's an error (e.g., insufficient balance)
                    $errors++;

                    continue;
                }
            }

            $recipeIndex++;

            if ($recipeIndex % 50 == 0) {
                echo "   ⏳ Processed {$recipeIndex} recipes... (Errors: {$errors})\n";
            }
        }

        echo "   ✅ Created {$ingredientCount} ingredients (Skipped: {$errors})\n\n";
    }

    private function getRandomItems(int $count): array
    {
        $shuffled = $this->items;
        shuffle($shuffled);

        return array_slice($shuffled, 0, min($count, count($this->items)));
    }
}
