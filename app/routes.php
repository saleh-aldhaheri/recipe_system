<?php

use App\Controllers\IngredientsController;
use App\Controllers\ItemsController;
use App\Controllers\RecipesController;
use App\Middleware\ImportMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('welcome');

    return $response;
});

// Items routes
$app->group('/items', function (RouteCollectorProxy $group) {
    $group->get('', [ItemsController::class, 'index']);
    $group->post('', [ItemsController::class, 'store']);
    $group->get('/{id}', [ItemsController::class, 'show']);
    $group->put('/{id}', [ItemsController::class, 'update']);
    $group->patch('/{id}', [ItemsController::class, 'update']);
    $group->delete('/{id}', [ItemsController::class, 'destroy']);
});

// Recipe routes
$app->group('/recipe', function (RouteCollectorProxy $group) {
    $group->get('', [RecipesController::class, 'index']);
    $group->post('', [RecipesController::class, 'store']);
    $group->get('/{id}', [RecipesController::class, 'show']);
    $group->put('/{id}', [RecipesController::class, 'update']);
    $group->patch('/{id}', [RecipesController::class, 'update']);
    $group->delete('/{id}', [RecipesController::class, 'destroy']);
    $group->post('/import', [RecipesController::class, 'import'])->add(new ImportMiddleware()); 
});

// Ingredients routes
$app->group('/ingredients', function (RouteCollectorProxy $group) {
    $group->get('', [IngredientsController::class, 'index']);
    $group->post('', [IngredientsController::class, 'store']);
    $group->get('/{id}', [IngredientsController::class, 'show']);
    $group->put('/{id}', [IngredientsController::class, 'update']);
    $group->patch('/{id}', [IngredientsController::class, 'update']);
    $group->delete('/{id}', [IngredientsController::class, 'destroy']);
});
