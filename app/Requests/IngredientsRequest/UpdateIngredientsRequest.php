<?php

namespace App\Requests\IngredientsRequest;

use App\Exceptions\ValidationException;
use App\Models\Ingredient;
use App\Models\Item;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateIngredientsRequest implements RequestInterface
{
    public function __construct(private int $ingredientId) {}

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

        $errors = [];
        $ingredient = Ingredient::with('recipe.ingredients.item')->find($this->ingredientId);
        $item = null;

        if (isset($data['item_id'])) {
            if (! is_numeric($data['item_id'])) {
                $errors['item_id'] = 'Item ID must be a number';
            } elseif ((int) $data['item_id'] <= 0) {
                $errors['item_id'] = 'Item ID must be a positive number';
            } else {
                $item = Item::find($data['item_id']);
                if (! $item) {
                    $errors['item_id'] = 'Item not found';
                }
            }
        }

        if (isset($data['quantity'])) {
            if (! is_numeric($data['quantity'])) {
                $errors['quantity'] = 'Quantity must be a number';
            } elseif ((float) $data['quantity'] <= 0) {
                $errors['quantity'] = 'Quantity must be greater than zero';
            } elseif ($item) {

                $currentIngredient = Ingredient::find($this->ingredientId);
                $availableBalance = (float) $item->balance;

                if ($currentIngredient && $currentIngredient->item_id == $item->id) {
                    $availableBalance += (float) $currentIngredient->quantity;
                }

                if ((float) $data['quantity'] > $availableBalance) {
                    $errors['quantity'] = "Quantity ({$data['quantity']}) cannot exceed available balance ({$availableBalance})";
                }
            }
        }

        if (isset($data['item_id']) && $item && $ingredient && $ingredient->recipe) {
            $recipe = $ingredient->recipe;
            $existingUnits = $recipe->ingredients
                ->where('id', '!=', $this->ingredientId)
                ->map(function ($ing) {
                    return $ing->item ? $ing->item->unit : null;
                })
                ->filter()
                ->unique()
                ->values();

            if ($existingUnits->count() === 1 && $existingUnits->first() !== $item->unit) {
                $errors['item_id'] = "Unit mismatch. Recipe uses '{$existingUnits->first()}' but item has '{$item->unit}'";
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        $validated = [];

        if (isset($data['item_id'])) {
            $validated['item_id'] = (int) $data['item_id'];
        }
        if (isset($data['quantity'])) {
            $validated['quantity'] = (float) $data['quantity'];
        }

        return $validated;
    }
}
