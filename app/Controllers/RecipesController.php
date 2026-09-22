<?php

namespace App\Controllers;

use App\Models\Recipe;
use App\Requests\IngredientsRequest\StoreIngredientsRequest;
use App\Requests\RecipesRequests\InputRecipesRequest;
use App\Requests\RecipesRequests\ShowRecipesRequest;
use App\Requests\RecipesRequests\StoreRecipeRequest;
use App\Requests\RecipesRequests\UpdateRecipeRequest;
use App\Services\ImportRecipeService;
use App\Services\ingredientsService;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecipesController extends BaseController
{
    public function __construct(
        private ingredientsService $ingredientService,
        private ImportRecipeService $importRecipeService
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $page = (int) ($queryParams['page'] ?? 1);
        $perPage = (int) ($queryParams['per_page'] ?? 10);
        $search = $queryParams['search'] ?? '';

        $query = Recipe::with('ingredients.item');

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

        $html = view('recipes.index', [
            'title' => 'Recipes Calendar',
            'currentPage' => 'recipes',
            'recipes' => $recipes['data'],
            'pagination' => $recipes['pagination'],
            'search' => $search,
            'scripts' => '<script src="'.asset('js/recipes.js').'?v='.filemtime(BASE.'public/js/recipes.js').'"></script>',
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

        $data = DB::connection()->transaction(function () use ($validated) {

            $recipe = Recipe::create([
                'name' => $validated['name'],
                'date' => $validated['date'],
            ]);

            $failed = ($this->ingredientService)->storeIngredients($validated['ingredients'], $recipe->id);

            return ['recipe' => $recipe->load('ingredients.item'), 'failed' => $failed];
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe created successfully',
            'data' => $data,
        ], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $recipe = Recipe::findOrFail($id);
        $validated = (new UpdateRecipeRequest($id, new StoreIngredientsRequest))->validate($request);

        $data = DB::connection()->transaction(function () use ($recipe, $validated) {
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

            $failed = [];
            if (isset($validated['ingredients'])) {
                $ingredientRequest = new StoreIngredientsRequest(false);
                $ingredientsData = [];

                foreach ($validated['ingredients'] as $ingredient) {
                    try {
                        $ingredientsData[] = $ingredientRequest->validateData($ingredient);
                    } catch (\App\Exceptions\ValidationException $e) {
                        $failed[] = [
                            'item_id' => $ingredient['item_id'] ?? null,
                            'quantity' => $ingredient['quantity'] ?? null,
                            'reason' => implode(', ', $e->getErrors()),
                        ];
                    }
                }

                if (! empty($ingredientsData)) {
                    $failed = array_merge($failed, ($this->ingredientService)->updateRecipeIngredients($recipe, $ingredientsData));
                } else {
                    $failed = array_merge($failed, ($this->ingredientService)->updateRecipeIngredients($recipe, []));
                }
            }

            return [
                'recipe' => $recipe->load('ingredients.item'),
                'failed' => $failed,
            ];
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe updated successfully',
            'data' => $data,
        ]);
    }

    public function import(Request $request, Response $response): Response
    {
        $validated = (new InputRecipesRequest)->validate($request);

        $files = $validated['files'];

        $result = $this->importRecipeService->processFiles($files);

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
            $ingredientsService = $this->ingredientService;
            foreach ($recipe->ingredients as $ingredient) {
                $ingredientsService->updateItemOnDelete($ingredient);
            }
            $recipe->delete();
        });

        return jsonResponse($response, [
            'success' => true,
            'message' => 'Recipe deleted successfully',
        ]);
    }
}
