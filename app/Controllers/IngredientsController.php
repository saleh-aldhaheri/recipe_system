<?php

namespace App\Controllers;

use App\Models\Ingredient;
use App\Requests\IngredientsRequest\ShowIngredientsRequest;
use App\Requests\IngredientsRequest\StoreIngredientsRequest;
use App\Requests\IngredientsRequest\UpdateIngredientsRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IngredientsController extends BaseController
{
    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int) ($queryParams['page'] ?? 1);
        $perPage = (int) ($queryParams['per_page'] ?? 10);
        $recipeId = $queryParams['recipe_id'] ?? null;
        $itemId = $queryParams['item_id'] ?? null;

        $query = Ingredient::with(['recipe', 'item']);

        if ($recipeId) {
            $query->where('recipe_id', (int) $recipeId);
        }

        if ($itemId) {
            $query->where('item_id', (int) $itemId);
        }

        $ingredients = $this->Paginate($query, $page, $perPage, 'id');

        return jsonResponse($response, [
            'success' => true,
            'data' => $ingredients['data'],
            'pagination' => $ingredients['pagination'],
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $validated = (new ShowIngredientsRequest($args))->validate($request);
        $id = $validated['id'];

        $ingredient = Ingredient::with(['recipe', 'item'])->findOrFail($id);

        return jsonResponse($response, [
            'success' => true,
            'data' => $ingredient,
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $validatedData = (new StoreIngredientsRequest)->validate($request);
        $ingredient = Ingredient::create($validatedData);
        $ingredient->load(['recipe', 'item']);

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Ingredient created successfully',
            'data' => $ingredient,
        ], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $ingredient = Ingredient::findOrFail($id);
        $validatedData = (new UpdateIngredientsRequest($id))->validate($request);

        $ingredient->update($validatedData);
        $ingredient->refresh();
        $ingredient->load(['recipe', 'item']);

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Ingredient updated successfully',
            'data' => $ingredient,
        ]);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Ingredient deleted successfully',
        ]);
    }
}
