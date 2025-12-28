# Quick Start Guide

## Running the Server

### Correct Command:
```bash
cd public
php -S localhost:8881
```

**OR** from project root:
```bash
php -S localhost:8881 -t public
```

### ❌ Wrong (what caused the error):
```bash
php -S localhost:8881 -t public clear  # Don't add "clear" here!
```

If you want to clear the terminal first, run them separately:
```bash
clear
php -S localhost:8881 -t public
```

## Setup Steps

### 1. Create `.env` file (copy from `.env.example`):
```bash
cp .env.example .env
```

### 2. Edit `.env` with your database credentials:
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

### 3. Import database schema:
```bash
mysql -u your_username -p your_database_name < database/schema.sql
```

### 4. Start the server:
```bash
php -S localhost:8881 -t public
```

## Available Routes

Once the server is running, visit:

- `http://localhost:8881/` - API information
- `http://localhost:8881/items` - Get all items (GET)
- `http://localhost:8881/items/{id}` - Get item by ID (GET)
- `http://localhost:8881/items` - Create item (POST)
- `http://localhost:8881/items/{id}` - Update item (PUT/PATCH)
- `http://localhost:8881/items/{id}` - Delete item (DELETE)

## Testing with cURL

### Get all items:
```bash
curl http://localhost:8881/items
```

### Create an item:
```bash
curl -X POST http://localhost:8881/items \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Item",
    "short_name": "TEST",
    "balance": 100.5,
    "unit": "KG"
  }'
```

### Get item by ID:
```bash
curl http://localhost:8881/items/1
```

## Troubleshooting

### Error: "Failed to open stream: No such file or directory"
- Make sure you're running the command from the correct directory
- Don't add extra words like "clear" to the PHP server command

### Error: Database connection failed
- Check your `.env` file exists and has correct credentials
- Make sure your database server is running
- Verify the database name exists

### Error: Class not found
- Run `composer install` to install dependencies
- Check that autoload is working: `composer dump-autoload`

