# Slim Framework Routing Guide

## Setup Complete! ✅

Slim Framework has been installed and configured. Here's how to use it:

## Quick Start

### 1. Access Your Routes

If using Apache with `.htaccess`:
- `http://localhost/xlsx/public/` - Home
- `http://localhost/xlsx/public/excel/parse` - Parse Excel
- `http://localhost/xlsx/public/recipes` - Get recipes

If using PHP built-in server:
```bash
cd public
php -S localhost:8000 app.php
```
Then access: `http://localhost:8000/`

### 2. Basic Routing Examples

#### GET Route
```php
$app->get('/path', function (Request $request, Response $response) {
    $response->getBody()->write('Hello World');
    return $response;
});
```

#### POST Route
```php
$app->post('/path', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    // Process $data
    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});
```

#### Route with Parameters
```php
$app->get('/recipes/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    // Use $id
    return $response;
});
```

#### Multiple HTTP Methods
```php
$app->map(['GET', 'POST'], '/path', function (Request $request, Response $response) {
    // Handle both GET and POST
    return $response;
});
```

### 3. Available Routes

Currently configured routes in `public/app.php`:

- `GET /` - API information
- `GET /excel/parse` - Parse Excel file and return JSON
- `GET /recipes` - Get all recipes (placeholder)
- `GET /recipes/{id}` - Get recipe by ID (placeholder)
- `POST /recipes` - Create recipe (placeholder)

### 4. JSON Response Example

```php
$app->get('/api/data', function (Request $request, Response $response) {
    $data = [
        'status' => 'success',
        'data' => ['item1', 'item2']
    ];
    
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});
```

### 5. Request Data

#### Get Query Parameters
```php
$app->get('/search', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();
    $search = $queryParams['q'] ?? '';
    // Use $search
    return $response;
});
```

#### Get POST Data
```php
$app->post('/submit', function (Request $request, Response $response) {
    $data = json_decode($request->getBody()->getContents(), true);
    // Process $data
    return $response;
});
```

### 6. Response Status Codes

```php
// 200 OK (default)
return $response;

// 201 Created
return $response->withStatus(201);

// 404 Not Found
return $response->withStatus(404);

// 500 Error
return $response->withStatus(500);
```

### 7. Organizing Routes

For better organization, you can create separate route files:

**routes/api.php:**
```php
<?php
use Slim\App;

return function (App $app) {
    $app->get('/api/recipes', function ($request, $response) {
        // ...
    });
};
```

**In app.php:**
```php
(require __DIR__ . '/../routes/api.php')($app);
```

## Next Steps

1. **Add Database Connection** - Create a database config file
2. **Create Controllers** - Move route logic to controller classes
3. **Add Middleware** - Authentication, CORS, etc.
4. **Error Handling** - Custom error handlers

## Documentation

- [Slim Framework Docs](https://www.slimframework.com/docs/v4/)
- [PSR-7 HTTP Messages](https://www.php-fig.org/psr/psr-7/)

