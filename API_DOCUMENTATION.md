# API Documentation

This document describes all AJAX endpoints available in the Recipe Management System API.

**Base URL**: `http://localhost:8881` (or your server URL)

All responses are in JSON format with the following structure:
- **Success responses**: `{ "success": true, "data": {...}, ... }`
- **Error responses**: `{ "success": false, "message": "...", "errors": {...} }`

---

## Table of Contents

1. [Items Endpoints](#items-endpoints)
2. [Recipes Endpoints](#recipes-endpoints)
3. [Ingredients Endpoints](#ingredients-endpoints)
4. [Error Responses](#error-responses)

---

## Items Endpoints

### 1. List Items

**GET** `/items`

Get a paginated list of items with optional search.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Items per page |
| `search` | string | No | - | Search by name or short_name |

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "short_name": "ITEM001",
            "name": "Item Name",
            "balance": 100.500,
            "unit": "kg",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 50,
        "last_page": 5
    },
    "search": "ITEM"
}
```

---

### 2. Get Single Item

**GET** `/items/{id}`

Get a single item by ID.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Item ID |

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "short_name": "ITEM001",
        "name": "Item Name",
        "balance": 100.500,
        "unit": "kg",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z"
    }
}
```

---

### 3. Create Item

**POST** `/items`

Create a new item.

#### Request Body

```json
{
    "name": "Item Name",
    "short_name": "ITEM001",
    "balance": 100.5,
    "unit": "kg"
}
```

#### Field Validation

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | Min 3, Max 255 characters |
| `short_name` | string | Yes | Max 50 characters, must be unique |
| `balance` | float | Yes | Must be positive or zero |
| `unit` | string | Yes | Max 20 characters |

#### Success Response (201)

```json
{
    "success": true,
    "message": "Item created successfully",
    "data": {
        "id": 1,
        "short_name": "ITEM001",
        "name": "Item Name",
        "balance": 100.500,
        "unit": "kg",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z"
    }
}
```

---

### 4. Update Item

**PUT/PATCH** `/items/{id}`

Update an existing item. All fields are optional - only provided fields will be updated.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Item ID |

#### Request Body (all fields optional)

```json
{
    "name": "Updated Item Name",
    "short_name": "ITEM002",
    "balance": 200.0,
    "unit": "pcs"
}
```

#### Success Response (200)

```json
{
    "success": true,
    "message": "Item updated successfully",
    "data": {
        "id": 1,
        "short_name": "ITEM002",
        "name": "Updated Item Name",
        "balance": 200.000,
        "unit": "pcs",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T11:00:00.000000Z"
    }
}
```

---

### 5. Delete Item

**DELETE** `/items/{id}`

Delete an item by ID.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Item ID |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Item deleted successfully"
}
```

---

## Recipes Endpoints

### 1. List Recipes

**GET** `/recipe`

Get a paginated list of recipes with optional search.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Recipes per page |
| `search` | string | No | - | Search by recipe name |

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Curry Laksa Paste",
            "date": "2025-12-28",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3
    },
    "search": "Curry"
}
```

---

### 2. Get Single Recipe

**GET** `/recipe/{id}`

Get a single recipe by ID with its ingredients and items.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Recipe ID |

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Curry Laksa Paste",
        "date": "2025-12-28",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z",
        "ingredients": [
            {
                "id": 1,
                "recipe_id": 1,
                "item_id": 1,
                "quantity": 10.500,
                "created_at": "2025-12-28T10:00:00.000000Z",
                "updated_at": "2025-12-28T10:00:00.000000Z",
                "item": {
                    "id": 1,
                    "short_name": "ITEM001",
                    "name": "Item Name",
                    "balance": 100.500,
                    "unit": "kg",
                    "created_at": "2025-12-28T10:00:00.000000Z",
                    "updated_at": "2025-12-28T10:00:00.000000Z"
                }
            }
        ]
    }
}
```

---

### 3. Create Recipe

**POST** `/recipe`

Create a new recipe with optional ingredients.

#### Request Body

```json
{
    "name": "Curry Laksa Paste",
    "date": "2025-12-28",
    "ingredients": [
        {
            "item_id": 1,
            "quantity": 10.5
        },
        {
            "item_id": 2,
            "quantity": 5.0
        }
    ]
}
```

#### Field Validation

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | Min 3, Max 255 characters |
| `date` | string | Yes | Format: Y-m-d (e.g., 2025-12-28) |
| `ingredients` | array | No | Array of ingredient objects |
| `ingredients[].item_id` | integer | Yes* | Required if ingredients provided, must exist |
| `ingredients[].quantity` | float | Yes* | Required if ingredients provided, must be positive or zero |

#### Success Response (201)

```json
{
    "success": true,
    "message": "Recipe created successfully",
    "data": {
        "id": 1,
        "name": "Curry Laksa Paste",
        "date": "2025-12-28",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z",
        "ingredients": [
            {
                "id": 1,
                "recipe_id": 1,
                "item_id": 1,
                "quantity": 10.500,
                "created_at": "2025-12-28T10:00:00.000000Z",
                "updated_at": "2025-12-28T10:00:00.000000Z",
                "item": {
                    "id": 1,
                    "short_name": "ITEM001",
                    "name": "Item Name",
                    "balance": 100.500,
                    "unit": "kg",
                    "created_at": "2025-12-28T10:00:00.000000Z",
                    "updated_at": "2025-12-28T10:00:00.000000Z"
                }
            }
        ]
    }
}
```

---

### 4. Update Recipe

**PUT/PATCH** `/recipe/{id}`

Update an existing recipe. All fields are optional - only provided fields will be updated.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Recipe ID |

#### Request Body (all fields optional)

```json
{
    "name": "Updated Recipe Name",
    "date": "2025-12-29",
    "ingredients": [
        {
            "item_id": 1,
            "quantity": 15.0
        }
    ]
}
```

**Note**: If `ingredients` is provided, all existing ingredients will be deleted and replaced with the new ones.

#### Success Response (200)

```json
{
    "success": true,
    "message": "Recipe updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Recipe Name",
        "date": "2025-12-29",
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T11:00:00.000000Z",
        "ingredients": [
            {
                "id": 2,
                "recipe_id": 1,
                "item_id": 1,
                "quantity": 15.000,
                "created_at": "2025-12-28T11:00:00.000000Z",
                "updated_at": "2025-12-28T11:00:00.000000Z",
                "item": {
                    "id": 1,
                    "short_name": "ITEM001",
                    "name": "Item Name",
                    "balance": 100.500,
                    "unit": "kg",
                    "created_at": "2025-12-28T10:00:00.000000Z",
                    "updated_at": "2025-12-28T10:00:00.000000Z"
                }
            }
        ]
    }
}
```

---

### 5. Import Recipes from Excel

**POST** `/recipe/import`

Import recipes from Excel files. Accepts multiple files.

#### Request

**Content-Type**: `multipart/form-data`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `files` | file(s) | Yes | Excel file(s) (.xlsx, .xls), max 10MB each, max 7 files |

**Note**: You can upload multiple files by using array notation in the form field name (e.g., `files[]`).

#### Excel File Format

The Excel file should contain:
- **DATE:** row with date in format Y-m-d (e.g., `DATE: 2025-12-28`)
- **PRODUCT:** row with recipe name (e.g., `PRODUCT: Curry Laksa Paste`)
- Tables with headers: **ITEM**, **BATCH NUMBER**, **Qty**

Example Excel structure:
```
DATE: 2025-12-28
PRODUCT: Curry Laksa Paste

ITEM    | BATCH NUMBER | Qty
ITEM001 | BATCH001    | 10.5
ITEM002 | BATCH002    | 5.0
```

#### Success Response (201)

**For multiple files processed:**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "processed": 2,
        "recipes": [
            {
                "file": "recipe1.xlsx",
                "status": "success",
                "recipe_id": 1,
                "recipe_name": "Curry Laksa Paste",
                "date": "2025-12-28",
                "ingredients_count": 5,
                "failed_ingredients": [
                    {
                        "short_name": "NEWITEM",
                        "name": "NEWITEM",
                        "quantity": 10.0,
                        "batch_number": "BATCH001",
                        "reason": "Item not found, created automatically"
                    }
                ]
            },
            {
                "file": "recipe2.xlsx",
                "status": "error",
                "message": "Product name not found in file",
                "failed_ingredients": []
            }
        ],
        "failed_ingredients": [
            {
                "short_name": "NEWITEM",
                "name": "NEWITEM",
                "quantity": 10.0,
                "batch_number": "BATCH001",
                "reason": "Item not found, created automatically"
            }
        ]
    }
}
```

**For single file processed:**

```json
{
    "success": true,
    "message": "Import completed",
    "data": {
        "file": "recipe1.xlsx",
        "status": "success",
        "recipe_id": 1,
        "recipe_name": "Curry Laksa Paste",
        "date": "2025-12-28",
        "ingredients_count": 5,
        "failed_ingredients": [
            {
                "short_name": "NEWITEM",
                "name": "NEWITEM",
                "quantity": 10.0,
                "batch_number": "BATCH001",
                "reason": "Item not found, created automatically"
            }
        ]
    }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `processed` | integer | Number of files processed (only for multiple files) |
| `recipes` | array | Array of processing results per file (only for multiple files) |
| `file` | string | Original filename |
| `status` | string | `success` or `error` |
| `recipe_id` | integer | Created/found recipe ID (if success) |
| `recipe_name` | string | Recipe name (if success) |
| `date` | string | Recipe date (if success) |
| `ingredients_count` | integer | Number of ingredients created (if success) |
| `failed_ingredients` | array | Items that were auto-created (if success) |
| `message` | string | Error message (if error) |
| `failed_ingredients` (root) | array | All failed ingredients across all files (only for multiple files) |

#### Item Matching Logic

Items are matched by `short_name` (from the Excel "ITEM" column). 

**If item is found:**
- Uses existing item
- Creates ingredient with the quantity from Excel

**If item is NOT found:**
- Automatically creates a new item with:
  - `short_name`: from Excel "ITEM" column
  - `name`: same as short_name
  - `balance`: 0.0
  - `unit`: "pcs"
- Creates ingredient with the quantity
- Tracks the item in `failed_ingredients` array

**Failed Ingredients Structure:**
```json
{
    "short_name": "ITEM001",
    "name": "ITEM001",
    "quantity": 10.0,
    "batch_number": "BATCH001",
    "reason": "Item not found, created automatically"
}
```

These auto-created items should be reviewed and updated with proper information (name, unit, etc.) after import.

---

### 6. Delete Recipe

**DELETE** `/recipe/{id}`

Delete a recipe by ID. This will also delete all associated ingredients (CASCADE).

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Recipe ID |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Recipe deleted successfully"
}
```

---

## Ingredients Endpoints

### 1. List Ingredients

**GET** `/ingredients`

Get a paginated list of ingredients with optional filtering.

#### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Ingredients per page |
| `recipe_id` | integer | No | - | Filter by recipe ID |
| `item_id` | integer | No | - | Filter by item ID |

#### Success Response (200)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "recipe_id": 1,
            "item_id": 1,
            "quantity": 10.500,
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z",
            "recipe": {
                "id": 1,
                "name": "Curry Laksa Paste",
                "date": "2025-12-28",
                "created_at": "2025-12-28T10:00:00.000000Z",
                "updated_at": "2025-12-28T10:00:00.000000Z"
            },
            "item": {
                "id": 1,
                "short_name": "ITEM001",
                "name": "Item Name",
                "balance": 100.500,
                "unit": "kg",
                "created_at": "2025-12-28T10:00:00.000000Z",
                "updated_at": "2025-12-28T10:00:00.000000Z"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 15,
        "last_page": 2
    }
}
```

---

### 2. Get Single Ingredient

**GET** `/ingredients/{id}`

Get a single ingredient by ID with its recipe and item.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Ingredient ID |

#### Success Response (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "recipe_id": 1,
        "item_id": 1,
        "quantity": 10.500,
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z",
        "recipe": {
            "id": 1,
            "name": "Curry Laksa Paste",
            "date": "2025-12-28",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        },
        "item": {
            "id": 1,
            "short_name": "ITEM001",
            "name": "Item Name",
            "balance": 100.500,
            "unit": "kg",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        }
    }
}
```

---

### 3. Create Ingredient

**POST** `/ingredients`

Create a new ingredient.

#### Request Body

```json
{
    "recipe_id": 1,
    "item_id": 1,
    "quantity": 10.5
}
```

#### Field Validation

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `recipe_id` | integer | Yes | Must exist in recipes table |
| `item_id` | integer | Yes | Must exist in items table |
| `quantity` | float | Yes | Must be positive or zero |

#### Success Response (201)

```json
{
    "success": true,
    "message": "Ingredient created successfully",
    "data": {
        "id": 1,
        "recipe_id": 1,
        "item_id": 1,
        "quantity": 10.500,
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T10:00:00.000000Z",
        "recipe": {
            "id": 1,
            "name": "Curry Laksa Paste",
            "date": "2025-12-28",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        },
        "item": {
            "id": 1,
            "short_name": "ITEM001",
            "name": "Item Name",
            "balance": 100.500,
            "unit": "kg",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        }
    }
}
```

---

### 4. Update Ingredient

**PUT/PATCH** `/ingredients/{id}`

Update an existing ingredient. All fields are optional - only provided fields will be updated.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Ingredient ID |

#### Request Body (all fields optional)

```json
{
    "item_id": 2,
    "quantity": 15.0
}
```

#### Success Response (200)

```json
{
    "success": true,
    "message": "Ingredient updated successfully",
    "data": {
        "id": 1,
        "recipe_id": 1,
        "item_id": 2,
        "quantity": 15.000,
        "created_at": "2025-12-28T10:00:00.000000Z",
        "updated_at": "2025-12-28T11:00:00.000000Z",
        "recipe": {
            "id": 1,
            "name": "Curry Laksa Paste",
            "date": "2025-12-28",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        },
        "item": {
            "id": 2,
            "short_name": "ITEM002",
            "name": "Another Item",
            "balance": 50.000,
            "unit": "pcs",
            "created_at": "2025-12-28T10:00:00.000000Z",
            "updated_at": "2025-12-28T10:00:00.000000Z"
        }
    }
}
```

---

### 5. Delete Ingredient

**DELETE** `/ingredients/{id}`

Delete an ingredient by ID.

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Ingredient ID |

#### Success Response (200)

```json
{
    "success": true,
    "message": "Ingredient deleted successfully"
}
```

---

## Error Responses

### Validation Error (422)

When validation fails, the API returns a 422 status code with validation errors:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": "Name is required",
        "short_name": "Short name already exists",
        "balance": "Balance must be positive"
    }
}
```

### Not Found Error (404)

When a resource is not found:

```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Database Error (500)

When a database error occurs:

```json
{
    "success": false,
    "message": "Database error occurred"
}
```

### Generic Error (500)

For other errors:

```json
{
    "success": false,
    "message": "An error occurred",
    "error": "Detailed error message"
}
```

### HTTP Exception (400, 403, etc.)

For HTTP exceptions (like bad request):

```json
{
    "success": false,
    "message": "Bad request message"
}
```

---

## Common Response Patterns

### Pagination Object

All list endpoints return a pagination object:

```json
{
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "last_page": 10
}
```

### Timestamp Format

All timestamps are in ISO 8601 format:
```
2025-12-28T10:00:00.000000Z
```

### Date Format

Recipe dates are in `Y-m-d` format:
```
2025-12-28
```

---

## Notes

1. **Content-Type**: All requests should use `Content-Type: application/json` except for file uploads which use `multipart/form-data`.

2. **Authentication**: Currently, no authentication is required. Add authentication middleware as needed.

3. **CORS**: If making requests from a browser, ensure CORS headers are configured.

4. **Rate Limiting**: Consider implementing rate limiting for production use.

5. **Item Matching**: When importing recipes, items are matched by `short_name`. If not found, items are auto-created with default values.

6. **Cascade Deletes**: 
   - Deleting a recipe will delete all its ingredients
   - Deleting an item will delete all ingredients using that item

---

## Example AJAX Requests

### JavaScript (Fetch API)

```javascript
// GET request
fetch('http://localhost:8881/items?page=1&per_page=10&search=ITEM')
    .then(response => response.json())
    .then(data => console.log(data));

// POST request
fetch('http://localhost:8881/items', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'Item Name',
        short_name: 'ITEM001',
        balance: 100.5,
        unit: 'kg'
    })
})
    .then(response => response.json())
    .then(data => console.log(data));

// File upload
const formData = new FormData();
formData.append('files', fileInput.files[0]);

fetch('http://localhost:8881/recipe/import', {
    method: 'POST',
    body: formData
})
    .then(response => response.json())
    .then(data => console.log(data));
```

### jQuery

```javascript
// GET request
$.ajax({
    url: 'http://localhost:8881/items',
    method: 'GET',
    data: { page: 1, per_page: 10, search: 'ITEM' },
    success: function(data) {
        console.log(data);
    }
});

// POST request
$.ajax({
    url: 'http://localhost:8881/items',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
        name: 'Item Name',
        short_name: 'ITEM001',
        balance: 100.5,
        unit: 'kg'
    }),
    success: function(data) {
        console.log(data);
    }
});
```

---

**Last Updated**: December 28, 2025

