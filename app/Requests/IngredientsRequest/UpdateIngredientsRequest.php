<?php

namespace App\Requests\IngredientsRequest;

use App\Exceptions\ValidationException;
use App\Models\Item;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class UpdateIngredientsRequest implements RequestInterface
{
    public function __construct(private int $ingredientId) {}

    public function validate(ServerRequestInterface|array $request): array
    {
        $data = $request->getParsedBody() ?? [];
        $errors = [];

        if (isset($data['item_id'])) {
            if (! is_numeric($data['item_id'])) {
                $errors['item_id'] = 'Item ID must be a number';
            } elseif ((int) $data['item_id'] <= 0) {
                $errors['item_id'] = 'Item ID must be a positive number';
            } elseif (! Item::find($data['item_id'])) {
                $errors['item_id'] = 'Item not found';
            }
        }

        if (isset($data['quantity'])) {
            if (! is_numeric($data['quantity'])) {
                $errors['quantity'] = 'Quantity must be a number';
            } elseif ((float) $data['quantity'] < 0) {
                $errors['quantity'] = 'Quantity must be positive or zero';
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
