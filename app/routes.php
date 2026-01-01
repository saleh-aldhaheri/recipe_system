<?php

use App\Controllers\DashboardController;
use App\Controllers\IngredientsController;
use App\Controllers\ItemsController;
use App\Controllers\RecipesController;
use App\Controllers\TransactionsController;
use App\Middleware\ImportMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', function (Request $request, Response $response) {
    require_once Core.'helper.php';

    $html = view('home.index', [
        'title' => 'Recipe Management System',
        'currentPage' => 'home',
    ]);

    $response->getBody()->write($html);

    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
});

$app->group('/items', function (RouteCollectorProxy $group) {
    $group->get('', [ItemsController::class, 'index']);
    $group->post('', [ItemsController::class, 'store']);
    $group->get('/{id}', [ItemsController::class, 'show']);
    $group->put('/{id}', [ItemsController::class, 'update']);
    $group->patch('/{id}', [ItemsController::class, 'update']);
    $group->delete('/{id}', [ItemsController::class, 'destroy']);
});

$app->group('/recipes', function (RouteCollectorProxy $group) {
    $group->get('', [RecipesController::class, 'index']);
    $group->post('', [RecipesController::class, 'store']);
    $group->get('/{id}', [RecipesController::class, 'show']);
    $group->put('/{id}', [RecipesController::class, 'update']);
    $group->patch('/{id}', [RecipesController::class, 'update']);
    $group->delete('/{id}', [RecipesController::class, 'destroy']);
    $group->post('/import', [RecipesController::class, 'import'])->add(new ImportMiddleware);
});

$app->group('/ingredients', function (RouteCollectorProxy $group) {
    $group->get('', [IngredientsController::class, 'index']);
    $group->post('', [IngredientsController::class, 'store']);
    $group->get('/{id}', [IngredientsController::class, 'show']);
    $group->put('/{id}', [IngredientsController::class, 'update']);
    $group->patch('/{id}', [IngredientsController::class, 'update']);
    $group->delete('/{id}', [IngredientsController::class, 'destroy']);
});

$app->get('/transactions', [TransactionsController::class, 'index']);

$app->group('/dashboard', function (RouteCollectorProxy $group) {

    $group->get('', [DashboardController::class, 'index']);

    $group->post('/items-summary', [DashboardController::class, 'getItemsSummary']);

    $group->post('/stats', [DashboardController::class, 'getStats']);

    $group->post('/transactions-by-type', [DashboardController::class, 'getTransactionsByType']);
    $group->post('/top-items', [DashboardController::class, 'getTopItems']);
    $group->post('/daily-trend', [DashboardController::class, 'getDailyTrend']);
    $group->post('/usage-by-recipes', [DashboardController::class, 'getUsageByRecipes']);

    $group->post('/item/{id}/recipes', [DashboardController::class, 'getItemRecipes']);
});
