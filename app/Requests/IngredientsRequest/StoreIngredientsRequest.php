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

    public function validate(ServerRequestInterface|array $request): array
    {
        $data = is_array($request) ? $request : ($request->getParsedBody() ?? []);
        $errors = [];

        if ($this->requireRecipeId) {
            if (! isset($data['recipe_id'])) {
                $errors['recipe_id'] = 'Recipe ID is required';
            } elseif (! is_numeric($data['recipe_id'])) {
                $errors['recipe_id'] = 'Recipe ID must be a number';
            } elseif ((int) $data['recipe_id'] <= 0) {
                $errors['recipe_id'] = 'Recipe ID must be a positive number';
            } elseif (! Recipe::find($data['recipe_id'])) {
                $errors['recipe_id'] = 'Recipe not found';
            }
        }

        if (! isset($data['item_id'])) {
            $errors['item_id'] = 'Item ID is required';
        } elseif (! is_numeric($data['item_id'])) {
            $errors['item_id'] = 'Item ID must be a number';
        } elseif ((int) $data['item_id'] <= 0) {
            $errors['item_id'] = 'Item ID must be a positive number';
        } elseif (! Item::find($data['item_id'])) {
            $errors['item_id'] = 'Item not found';
        }

        if (! isset($data['quantity'])) {
            $errors['quantity'] = 'Quantity is required';
        } elseif (! is_numeric($data['quantity'])) {
            $errors['quantity'] = 'Quantity must be a number';
        } elseif ((float) $data['quantity'] < 0) {
            $errors['quantity'] = 'Quantity must be positive or zero';
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
