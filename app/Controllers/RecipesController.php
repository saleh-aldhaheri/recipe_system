<?php

namespace App\Controllers;

use App\Models\Item;
use App\Models\Recipe;
use App\Requests\IngredientsRequest\StoreIngredientsRequest;
use App\Requests\RecipesRequests\InputRecipesRequest;
use App\Requests\RecipesRequests\ShowRecipesRequest;
use App\Requests\RecipesRequests\StoreRecipeRequest;
use App\Requests\RecipesRequests\UpdateRecipeRequest;
use App\Services\ImportRecipeService;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecipesController extends BaseController
{
    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int) ($queryParams['page'] ?? 1);
        $perPage = (int) ($queryParams['per_page'] ?? 10);
        $search = $queryParams['search'] ?? '';

        $query = Recipe::query();

        if (! empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $recipes = $this->Paginate($query, $page, $perPage, 'name');

        if (isAjaxRequest($request)) {
            return jsonResponse($response, [
                'success' => true,
                'data' => $recipes['data'],
                'pagination' => $recipes['pagination'],
                'search' => $search,
            ]);
        }

        $html = view('recipes.calendar', [
            'title' => 'Recipes Calendar',
            'currentPage' => 'recipes',
            'recipes' => $recipes['data'],
            'pagination' => $recipes['pagination'],
            'search' => $search,
            'scripts' => '<script src="'.asset('js/recipes.js').'"></script>',
        ]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $validated = (new ShowRecipesRequest($args))->validate($request);
        $id = $validated['id'];

        $recipe = Recipe::with('ingredients.item')->findOrFail($id);

        return jsonResponse($response, [
            'success' => true,
            'data' => $recipe,
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $validated = (new StoreRecipeRequest(new StoreIngredientsRequest))->validate($request);

        $recipe = DB::connection()->transaction(function () use ($validated) {

            $existingRecipe = Recipe::where('date', $validated['date'])->first();
            if ($existingRecipe) {
                throw new \App\Exceptions\ValidationException([
                    'date' => 'A recipe already exists for this date. Only one recipe per date is allowed.',
                ]);
            }

            $recipe = Recipe::create([
                'name' => $validated['name'],
                'date' => $validated['date'],
            ]);

            if (! empty($validated['ingredients'])) {
                $ingredientRequest = new StoreIngredientsRequest(false);
                $ingredientsData = [];

                foreach ($validated['ingredients'] as $ingredient) {
                    $validatedIngredient = $ingredientRequest->validateData($ingredient);
                    $ingredientsData[] = $validatedIngredient;

                    $item = Item::findOrFail($validatedIngredient['item_id']);
                    if ((float) $item->balance < (float) $validatedIngredient['quantity']) {
                        throw new \App\Exceptions\ValidationException([
                            'ingredients' => "Insufficient balance for item '{$item->name}'. Available: {$item->balance}, Required: {$validatedIngredient['quantity']}",
                        ]);
                    }
                    $item->balance -= (float) $validatedIngredient['quantity'];
                    $item->save();
                }

                $recipe->ingredients()->createMany($ingredientsData);
            }

            return $recipe->load('ingredients.item');
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe created successfully',
            'data' => $recipe,
        ], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $recipe = Recipe::findOrFail($id);
        $validated = (new UpdateRecipeRequest($id, new StoreIngredientsRequest))->validate($request);

        $recipe = DB::connection()->transaction(function () use ($recipe, $validated) {
            if (isset($validated['name']) || isset($validated['date'])) {
                $updateData = [];
                if (isset($validated['name'])) {
                    $updateData['name'] = $validated['name'];
                }
                if (isset($validated['date'])) {
                    $existingRecipe = Recipe::where('date', $validated['date'])
                        ->where('id', '!=', $recipe->id)
                        ->first();
                    if ($existingRecipe) {
                        throw new \App\Exceptions\ValidationException([
                            'date' => 'A recipe already exists for this date. Only one recipe per date is allowed.',
                        ]);
                    }
                    $updateData['date'] = $validated['date'];
                }
                $recipe->update($updateData);
            }

            if (isset($validated['ingredients'])) {

                $oldIngredients = $recipe->ingredients;
                foreach ($oldIngredients as $oldIngredient) {
                    $oldItem = Item::find($oldIngredient->item_id);
                    if ($oldItem) {
                        $oldItem->balance += (float) $oldIngredient->quantity;
                        $oldItem->save();
                    }
                }

                $recipe->ingredients()->delete();

                if (! empty($validated['ingredients'])) {
                    $ingredientRequest = new StoreIngredientsRequest(false);
                    $ingredientsData = [];

                    foreach ($validated['ingredients'] as $ingredient) {
                        $validatedIngredient = $ingredientRequest->validateData($ingredient);
                        $ingredientsData[] = $validatedIngredient;

                        $item = Item::findOrFail($validatedIngredient['item_id']);
                        if ((float) $item->balance < (float) $validatedIngredient['quantity']) {
                            throw new \App\Exceptions\ValidationException([
                                'ingredients' => "Insufficient balance for item '{$item->name}'. Available: {$item->balance}, Required: {$validatedIngredient['quantity']}",
                            ]);
                        }
                        $item->balance -= (float) $validatedIngredient['quantity'];
                        $item->save();
                    }

                    $recipe->ingredients()->createMany($ingredientsData);
                }
            }

            return $recipe->load('ingredients.item');
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe updated successfully',
            'data' => $recipe,
        ]);
    }

    public function import(Request $request, Response $response): Response
    {
        $validated = (new InputRecipesRequest)->validate($request);

        $files = $validated['files'];

        $result = (new ImportRecipeService)->processFiles($files);

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Import completed',
            'data' => [
                'processed' => count($result['results']),
                'recipes' => $result['results'],
                'failed_ingredients' => $result['failed_ingredients'],
            ],
        ], 201);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $recipe = Recipe::with('ingredients')->findOrFail($id);

        DB::connection()->transaction(function () use ($recipe) {

            foreach ($recipe->ingredients as $ingredient) {
                $item = Item::find($ingredient->item_id);
                if ($item) {
                    $item->balance += (float) $ingredient->quantity;
                    $item->save();
                }
            }

            $recipe->delete();
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe deleted successfully',
        ]);
    }
}
