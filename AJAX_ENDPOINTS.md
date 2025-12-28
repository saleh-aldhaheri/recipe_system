# Items API Endpoints - AJAX Guide

All endpoints return JSON responses with a consistent structure:

## Response Format

### Success Response:
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response:
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... }  // For validation errors
}
```

---

## Endpoints

### 1. Get All Items (with pagination & search)
**GET** `/items?page=1&per_page=10&search=test`

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 10)
- `search` (optional): Search term for name or short_name

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Item Name",
            "short_name": "ITEM",
            "balance": 100.5,
            "unit": "KG"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 50,
        "last_page": 5
    },
    "search": "test"
}
```

**AJAX Example:**
```javascript
fetch('/items?page=1&per_page=10&search=test')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Items:', data.data);
            console.log('Total:', data.pagination.total);
        }
    });
```

---

### 2. Get Single Item
**GET** `/items/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Item Name",
        "short_name": "ITEM",
        "balance": 100.5,
        "unit": "KG"
    }
}
```

**AJAX Example:**
```javascript
fetch('/items/1')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Item:', data.data);
        } else {
            console.error('Error:', data.message);
        }
    });
```

---

### 3. Create New Item
**POST** `/items`

**Request Body (JSON):**
```json
{
    "name": "Item Name",
    "short_name": "ITEM",
    "balance": 100.5,
    "unit": "KG"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Item created successfully",
    "data": {
        "id": 1,
        "name": "Item Name",
        "short_name": "ITEM",
        "balance": 100.5,
        "unit": "KG"
    }
}
```

**Validation Error Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": "Name is required",
        "short_name": "Short name already exists"
    }
}
```

**AJAX Example:**
```javascript
fetch('/items', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'Item Name',
        short_name: 'ITEM',
        balance: 100.5,
        unit: 'KG'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Item created:', data.data);
    } else {
        console.error('Validation errors:', data.errors);
    }
})
.catch(error => console.error('Error:', error));
```

---

### 4. Update Item
**PUT** `/items/{id}` or **PATCH** `/items/{id}`

**Request Body (JSON):**
```json
{
    "name": "Updated Name",
    "short_name": "UPDATED",
    "balance": 200.0,
    "unit": "L"
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Item updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Name",
        "short_name": "UPDATED",
        "balance": 200.0,
        "unit": "L"
    }
}
```

**AJAX Example:**
```javascript
fetch('/items/1', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        name: 'Updated Name',
        balance: 200.0
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Item updated:', data.data);
    } else {
        console.error('Errors:', data.errors);
    }
});
```

---

### 5. Delete Item
**DELETE** `/items/{id}`

**Success Response:**
```json
{
    "success": true,
    "message": "Item deleted successfully"
}
```

**AJAX Example:**
```javascript
fetch('/items/1', {
    method: 'DELETE'
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Item deleted successfully');
    } else {
        console.error('Error:', data.message);
    }
});
```

---

## Complete AJAX Example (jQuery)

```javascript
// Get all items
$.ajax({
    url: '/items',
    method: 'GET',
    data: { page: 1, per_page: 10, search: '' },
    success: function(response) {
        if (response.success) {
            response.data.forEach(item => {
                console.log(item.name);
            });
        }
    }
});

// Create item
$.ajax({
    url: '/items',
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
        name: 'New Item',
        short_name: 'NEW',
        balance: 50,
        unit: 'KG'
    }),
    success: function(response) {
        if (response.success) {
            alert('Item created!');
        } else {
            // Show validation errors
            Object.keys(response.errors).forEach(field => {
                console.error(field + ': ' + response.errors[field]);
            });
        }
    },
    error: function(xhr) {
        console.error('Request failed');
    }
});

// Update item
$.ajax({
    url: '/items/1',
    method: 'PUT',
    contentType: 'application/json',
    data: JSON.stringify({
        name: 'Updated Name',
        balance: 100
    }),
    success: function(response) {
        if (response.success) {
            console.log('Updated:', response.data);
        }
    }
});

// Delete item
$.ajax({
    url: '/items/1',
    method: 'DELETE',
    success: function(response) {
        if (response.success) {
            alert('Item deleted!');
        }
    }
});
```

---

## Complete AJAX Example (Vanilla JavaScript)

```javascript
// Helper function for AJAX requests
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Request failed:', error);
        return { success: false, message: 'Request failed' };
    }
}

// Usage examples
(async () => {
    // Get all items
    const items = await apiRequest('/items?page=1&per_page=10');
    if (items.success) {
        console.log('Items:', items.data);
    }
    
    // Create item
    const newItem = await apiRequest('/items', 'POST', {
        name: 'New Item',
        short_name: 'NEW',
        balance: 50,
        unit: 'KG'
    });
    if (newItem.success) {
        console.log('Created:', newItem.data);
    } else {
        console.error('Errors:', newItem.errors);
    }
    
    // Update item
    const updated = await apiRequest('/items/1', 'PUT', {
        name: 'Updated Name'
    });
    
    // Delete item
    const deleted = await apiRequest('/items/1', 'DELETE');
})();
```

---

## HTTP Status Codes

- `200` - Success (GET, PUT, PATCH, DELETE)
- `201` - Created (POST)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Validation Rules

- **name**: Required, min 3 characters, max 255 characters
- **short_name**: Required, max 50 characters, must be unique
- **balance**: Optional, must be numeric and positive
- **unit**: Required, max 20 characters

