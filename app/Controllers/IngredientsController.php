<?php

namespace App\Controllers;

use App\Models\Ingredient;
use App\Models\Item;
use App\Requests\IngredientsRequest\ShowIngredientsRequest;
use App\Requests\IngredientsRequest\StoreIngredientsRequest;
use App\Requests\IngredientsRequest\UpdateIngredientsRequest;
use Illuminate\Database\Capsule\Manager as DB;
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

        if (isAjaxRequest($request)) {
            return jsonResponse($response, [
                'success' => true,
                'data' => $ingredients['data'],
                'pagination' => $ingredients['pagination'],
            ]);
        }

        $html = view('ingredients.index', [
            'title' => 'Manage Ingredients',
            'currentPage' => 'ingredients',
            'ingredients' => $ingredients['data'],
            'pagination' => $ingredients['pagination'],
            'scripts' => '<script src="'.asset('js/ingredients.js').'"></script>',
        ]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
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

        $ingredient = DB::connection()->transaction(function () use ($validatedData) {
            $item = Item::findOrFail($validatedData['item_id']);

            if ((float) $item->balance < (float) $validatedData['quantity']) {
                throw new \App\Exceptions\ValidationException([
                    'quantity' => "Insufficient balance. Available: {$item->balance}, Required: {$validatedData['quantity']}",
                ]);
            }

            $item->balance -= (float) $validatedData['quantity'];
            $item->save();

            $ingredient = Ingredient::create($validatedData);

            return $ingredient->load(['recipe', 'item']);
        });

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

        $ingredient = DB::connection()->transaction(function () use ($ingredient, $validatedData) {
            $oldQuantity = (float) $ingredient->quantity;
            $oldItemId = $ingredient->item_id;
            $newQuantity = isset($validatedData['quantity']) ? (float) $validatedData['quantity'] : $oldQuantity;
            $newItemId = isset($validatedData['item_id']) ? (int) $validatedData['item_id'] : $oldItemId;

            if ($oldItemId === $newItemId) {
                $item = Item::findOrFail($newItemId);
                $item->balance += $oldQuantity; 
                
                if ($newQuantity > $item->balance) {
                    throw new \App\Exceptions\ValidationException([
                        'quantity' => "Insufficient balance. Available: {$item->balance}, Required: {$newQuantity}",
                    ]);
                }
                
                $item->balance -= $newQuantity;
                $item->save();
            } else {

                $oldItem = Item::findOrFail($oldItemId);
                $oldItem->balance += $oldQuantity;
                $oldItem->save();

                $newItem = Item::findOrFail($newItemId);
                if ($newQuantity > $newItem->balance) {
                    throw new \App\Exceptions\ValidationException([
                        'quantity' => "Insufficient balance. Available: {$newItem->balance}, Required: {$newQuantity}",
                    ]);
                }
                $newItem->balance -= $newQuantity;
                $newItem->save();
            }

            $ingredient->update($validatedData);

            return $ingredient->load(['recipe', 'item']);
        });

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

        DB::connection()->transaction(function () use ($ingredient) {
            $item = Item::findOrFail($ingredient->item_id);
            $item->balance += (float) $ingredient->quantity;
            $item->save();

            $ingredient->delete();
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Ingredient deleted successfully',
        ]);
    }
}
