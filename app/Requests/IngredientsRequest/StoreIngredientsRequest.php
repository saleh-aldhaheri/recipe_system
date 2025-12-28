<?php

namespace App\Requests\IngredientsRequest;

use App\Exceptions\ValidationException;
use App\Models\Item;
use App\Models\Recipe;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoreIngredientsRequest implements RequestInterface
{
    private bool $requireRecipeId;

    public function __construct(bool $requireRecipeId = true)
    {
        $this->requireRecipeId = $requireRecipeId;
    }

    public function validate(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody() ?? [];

        if (empty($data)) {
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $body = $request->getBody()->getContents();
                if (! empty($body)) {
                    $jsonData = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $jsonData;
                    }
                }
            }
        }

        return $this->validateData($data);
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if ($this->requireRecipeId) {
            if (! isset($data['recipe_id'])) {
                $errors['recipe_id'] = 'Recipe ID is required';
            } elseif (! is_numeric($data['recipe_id'])) {
                $errors['recipe_id'] = 'Recipe ID must be a number';
            } elseif ((int) $data['recipe_id'] <= 0) {
                $errors['recipe_id'] = 'Recipe ID must be a positive number';
            } else {
                $recipe = Recipe::find($data['recipe_id']);
                if (! $recipe) {
                    $errors['recipe_id'] = 'Recipe not found';
                } elseif (isset($data['item_id']) && is_numeric($data['item_id']) && (int) $data['item_id'] > 0) {
                    $existingIngredient = \App\Models\Ingredient::where('recipe_id', (int) $data['recipe_id'])
                        ->where('item_id', (int) $data['item_id'])
                        ->first();
                    if ($existingIngredient) {
                        $errors['item_id'] = 'This item is already added to this recipe';
                    }
                }
            }
        }

        $item = null;
        if (! isset($data['item_id'])) {
            $errors['item_id'] = 'Item ID is required';
        } elseif (! is_numeric($data['item_id'])) {
            $errors['item_id'] = 'Item ID must be a number';
        } elseif ((int) $data['item_id'] <= 0) {
            $errors['item_id'] = 'Item ID must be a positive number';
        } else {
            $item = Item::find($data['item_id']);
            if (! $item) {
                $errors['item_id'] = 'Item not found';
            }
        }

        if (! isset($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required';
        } elseif (! is_numeric($data['quantity'])) {
            $errors['quantity'] = 'Quantity must be a number';
        } elseif ((float) $data['quantity'] <= 0) {
            $errors['quantity'] = 'Quantity must be greater than zero';
        } elseif ($item && (float) $data['quantity'] > (float) $item->balance) {
            $errors['quantity'] = "Quantity ({$data['quantity']}) cannot exceed item balance ({$item->balance})";
        }

        if ($this->requireRecipeId && isset($data['recipe_id']) && $item) {
            $recipe = Recipe::with('ingredients.item')->find($data['recipe_id']);
            if ($recipe && $recipe->ingredients->count() > 0) {
                $existingUnits = $recipe->ingredients->map(function ($ing) {
                    return $ing->item ? $ing->item->unit : null;
                })->filter()->unique()->values();

                if ($existingUnits->count() === 1 && $existingUnits->first() !== $item->unit) {
                    $errors['item_id'] = "Unit mismatch. Recipe uses '{$existingUnits->first()}' but item has '{$item->unit}'";
                }
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        $validated = [
            'item_id' => (int) $data['item_id'],
            'quantity' => (float) $data['quantity'],
        ];

        if ($this->requireRecipeId && isset($data['recipe_id'])) {
            $validated['recipe_id'] = (int) $data['recipe_id'];
        }

        return $validated;
    }
}
