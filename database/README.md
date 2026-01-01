# Database Seeder

This directory contains database seeding scripts to populate the database with test data.

## Usage

### Basic Seeding (Add to existing data)
```bash
php database/seed.php
```

### Clear and Seed (Remove all existing data first)
```bash
php database/seed.php --clear
```

## What Gets Seeded

The seeder will create:

- **30 Items** - Various food items (meat, vegetables, spices, etc.)
  - Each item has a name, short name, unit, and initial balance
  
- **~730 Recipes** - Spread over the last year
  - 1-3 recipes per day randomly
  - Recipes are distributed across 365 days
  
- **~3000+ Ingredients** - 3-8 ingredients per recipe
  - Randomly selected items
  - Random quantities between 0.5 and 10.0
  
- **Transactions** - Automatically created via model events
  - Created when ingredients are added
  - `created_at` matches the recipe date

## Notes

- The seeder respects item balances - it won't create ingredients if there's insufficient balance
- Transactions are automatically created through the `Ingredient` model's boot events
- Recipe dates are spread over the last 365 days for realistic dashboard testing
- Some ingredients may be skipped if items don't have enough balance

## Example Output

```
🌱 Starting database seeding...

📦 Seeding Items...
   ✓ Created: Chicken Breast (CH-B)
   ✓ Created: Chicken Thigh (CH-T)
   ...
   ✅ Created 30 items

🍳 Seeding Recipes...
   ✅ Created 730 recipes

🥘 Seeding Ingredients...
   ⏳ Processed 50 recipes... (Errors: 5)
   ⏳ Processed 100 recipes... (Errors: 12)
   ...
   ✅ Created 2850 ingredients (Skipped: 45)

✅ Database seeding completed!
📊 Summary:
   - Items: 30
   - Recipes: 730
   - Total Ingredients: 2850
   - Total Transactions: 2850
```

