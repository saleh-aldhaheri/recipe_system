# Recipe Management System

A comprehensive web-based application for managing recipes, inventory items, ingredients, and tracking transactions in a professional kitchen or food production environment.

## 📋 Overview

The Recipe Management System is designed to help organizations efficiently manage their recipe planning, ingredient tracking, and inventory management. The system provides a clean, modern interface with real-time data visualization, automated inventory tracking, and Excel import capabilities.

## ✨ Features

### Core Functionality
- **Items Management**: Create, update, and manage inventory items with balance tracking
- **Recipes Management**: Plan and organize recipes with calendar-based scheduling
- **Ingredients Management**: Link ingredients to recipes with automatic inventory deduction
- **Transaction History**: Complete audit trail of all inventory movements
- **Dashboard Analytics**: Visual insights with charts and statistics

### Key Capabilities
- 📅 **Calendar View**: Weekly recipe calendar for easy meal planning
- 📊 **Real-time Dashboard**: Interactive charts showing usage trends and statistics
- 📥 **Excel Import**: Bulk import recipes from Excel files (up to 7 files at once)
- 🔍 **Advanced Search**: Search and filter across all modules
- 📱 **Responsive Design**: Works seamlessly on desktop and mobile devices
- 🎨 **Modern UI**: Clean, professional interface built with Tailwind CSS
- 📈 **Data Visualization**: Multiple chart types (pie, bar, line charts)

## 🛠️ Technology Stack

### Backend
- **PHP 8.0+**: Core server-side language
- **Slim Framework 4**: Lightweight PHP framework for routing and middleware
- **Illuminate Database**: Laravel's Eloquent ORM for database operations
- **PHPOffice PhpSpreadsheet**: Excel file processing
- **PHP-DI**: Dependency injection container

### Frontend
- **Tailwind CSS**: Utility-first CSS framework
- **JavaScript (Vanilla)**: No framework dependencies
- **Chart.js**: Data visualization library
- **Choices.js**: Enhanced select dropdowns
- **Material Symbols**: Icon library

### Database
- **MySQL/MariaDB**: Relational database management system

## 📦 Installation

### Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL/MariaDB
- Web server (Apache/Nginx) or PHP built-in server

### if you want to run the application with docker

### Step 1: Clone the Repository and run the docker compose
```bash
git clone https://github.com/salehnevergiveup/recipe_system.git
cd recipe_system
docker compose -p Recipe up -d
```
## if you want to run in the localhost without docker
### Step 1: Clone the Repository and run the docker compose
```bash
git clone https://github.com/salehnevergiveup/recipe_system.git
cd recipe_system
```

### Step 2: Install Dependencies
```bash
composer install
```

### Step 3: Environment Configuration
Create a `.env` file in the project root:
```bash
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=
```

### Step 4: Database Setup
Import the database schema:
```bash
mysql -u your_username -p your_database_name < schema.sql
```

### Step 5: Start the Server
Using PHP built-in server:
```bash
php -S localhost:8881 -t public
```

Or configure your web server to point to the `public` directory.

### Step 6: Access the Application
Open your browser and navigate to:
```
http://localhost:8881
```

## 📁 Project Structure

```
xlsx/
├── app/
│   ├── Controllers/          # Request handlers
│   │   ├── DashboardController.php
│   │   ├── ItemsController.php
│   │   ├── RecipesController.php
│   │   ├── IngredientsController.php
│   │   └── TransactionsController.php
│   ├── Models/               # Eloquent models
│   │   ├── Item.php
│   │   ├── Recipe.php
│   │   ├── Ingredient.php
│   │   └── Transaction.php
│   ├── Services/             # Business logic
│   │   ├── ItemsService.php
│   │   ├── RecipesService.php
│   │   ├── IngredientsService.php
│   │   ├── TransactionsService.php
│   │   └── DashboardService.php
│   ├── Requests/             # Request validation
│   ├── Middleware/           # HTTP middleware
│   ├── Enums/                # Enumerations
│   └── routes.php            # Route definitions
├── public/
│   ├── index.php             # Entry point
│   ├── js/                   # JavaScript files
│   │   ├── api.js
│   │   ├── dashboard.js
│   │   ├── items.js
│   │   ├── recipes.js
│   │   ├── ingredients.js
│   │   ├── transactions.js
│   │   └── notifications.js
│   └── css/                  # Stylesheets
├── view/                     # PHP templates
│   ├── layouts/
│   │   └── main.view.php
│   ├── home/
│   ├── dashboard/
│   ├── items/
│   ├── recipes/
│   ├── ingredients/
│   └── transactions/
├── core/                     # Core utilities
├── database/                 # Database migrations/seeders
├── bootstrap.php             # Application bootstrap
├── schema.sql                # Database schema
└── composer.json             # PHP dependencies
```

## 🎯 Usage Guide

### Managing Items
1. Navigate to **Items** from the sidebar
2. Click **Add New Item** to create inventory items
3. Set item name, short name, balance, and unit
4. Use the search bar to find items quickly
5. Filter by stock status (In Stock/Out of Stock)

### Creating Recipes
1. Go to **Recipes** to view the calendar
2. Click on any day to add a recipe
3. Enter recipe name and date
4. Add ingredients with quantities
5. The system automatically deducts quantities from item balances

### Importing Recipes
1. Click **Import Recipes** button
2. Select Excel files (up to 7 files)
3. System processes and imports recipes automatically
4. View imported recipes in the calendar

### Viewing Dashboard
1. Access **Dashboard** from the sidebar
2. View statistics and charts
3. Use the time frame selector to filter data
4. Click on items to view associated recipes

### Tracking Transactions
1. Navigate to **Transactions** to see all inventory movements
2. Search by item, recipe, type, or operation
3. View complete audit trail with before/after balances

## 🔌 API Endpoints

### Items
- `GET /items` - List all items (with pagination)
- `GET /items/{id}` - Get item by ID
- `POST /items` - Create new item
- `PUT /items/{id}` - Update item
- `DELETE /items/{id}` - Delete item

### Recipes
- `GET /recipes` - List recipes (with date filtering)
- `GET /recipes/{id}` - Get recipe by ID
- `POST /recipes` - Create new recipe
- `PUT /recipes/{id}` - Update recipe
- `DELETE /recipes/{id}` - Delete recipe
- `POST /recipes/import` - Import recipes from Excel

### Ingredients
- `GET /ingredients` - List ingredients (with filtering)
- `GET /ingredients/{id}` - Get ingredient by ID
- `POST /ingredients` - Create new ingredient
- `PUT /ingredients/{id}` - Update ingredient
- `DELETE /ingredients/{id}` - Delete ingredient

### Transactions
- `GET /transactions` - List all transactions (with pagination and search)

### Dashboard
- `POST /dashboard/stats` - Get dashboard statistics
- `POST /dashboard/transactions-by-type` - Get transaction type breakdown
- `POST /dashboard/top-items` - Get top items by usage
- `POST /dashboard/daily-trend` - Get daily usage trend
- `POST /dashboard/usage-by-recipes` - Get top recipes by usage
- `POST /dashboard/items-summary` - Get items summary table

## 🗄️ Database Schema

### Tables

#### `items`
- `id` - Primary key
- `name` - Item full name
- `short_name` - Item short name (unique)
- `balance` - Current inventory balance
- `unit` - Unit of measurement
- `created_at`, `updated_at` - Timestamps

#### `recipes`
- `id` - Primary key
- `name` - Recipe name
- `date` - Recipe date
- `created_at`, `updated_at` - Timestamps

#### `ingredients`
- `id` - Primary key
- `recipe_id` - Foreign key to recipes
- `item_id` - Foreign key to items
- `quantity` - Quantity used
- `created_at`, `updated_at` - Timestamps

#### `transactions`
- `id` - Primary key
- `item_id` - Foreign key to items
- `recipe_id` - Foreign key to recipes (nullable)
- `type` - Transaction type (recipe_usage, update_item)
- `operation` - Operation type (+ or -)
- `qty_used` - Quantity used
- `balance_before` - Balance before transaction
- `balance_after` - Balance after transaction
- `created_at` - Timestamp

## 🎨 UI Features

### Design System
- **Color Scheme**: Professional blue theme (#2b6cee) with light backgrounds
- **Typography**: Inter font family for clean readability
- **Components**: Consistent buttons, forms, modals, and tables
- **Responsive**: Mobile-first design approach

### Interactive Elements
- Collapsible sidebar navigation
- Real-time search and filtering
- Pagination for large datasets
- Modal dialogs for forms
- Toast notifications for user feedback
- Confirmation dialogs for destructive actions

## 🔧 Development

### Code Style
The project uses Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

### Adding New Features
1. Create controller in `app/Controllers/`
2. Add service logic in `app/Services/`
3. Create request validators in `app/Requests/`
4. Add routes in `app/routes.php`
5. Create views in `view/` directory
6. Add JavaScript in `public/js/`

### Database Migrations
Database schema is managed through `schema.sql`. For changes:
1. Update `schema.sql`
2. Export new schema or create migration scripts

## 📝 License

This project is proprietary software developed by saleh.

## 👥 Authors

- **saleh**

## 🤝 Contributing

This is a private project. For contributions or questions, please contact the project maintainers.

## 📞 Support

For support and inquiries, please contact the project maintainer.

---

**Version**: 1.0.0  
**Last Updated**: 2026

