<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Models\Item;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoreRecipeRequest implements RequestInterface
{
    public function __construct(
        private RequestInterface $storeIngredients
    ) {}

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

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 3) {
            $errors['name'] = 'Name must be at least 3 characters';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'Name must be less than 255 characters';
        }

        if (empty($data['date'])) {
            $errors['date'] = 'Date is required';
        } else {
            $dateString = trim($data['date']);
            $date = \DateTime::createFromFormat('Y-m-d', $dateString);

            if (! $date || $date->format('Y-m-d') !== $dateString) {
                $errors['date'] = 'Date must be in Y-m-d format (e.g., 2025-12-28)';
            } else {
                $existingRecipe = \App\Models\Recipe::where('date', $dateString)->first();
                if ($existingRecipe) {
                    $errors['date'] = 'A recipe already exists for this date. Only one recipe per date is allowed.';
                }
            }
        }

        $ingredientsErrors = [];
        if (isset($data['ingredients']) && ! empty($data['ingredients'])) {
            if (! is_array($data['ingredients'])) {
                $errors['ingredients'] = 'Ingredients must be an array';
            } else {
                foreach ($data['ingredients'] as $key => $ingredient) {
                    if (! is_array($ingredient)) {
                        $ingredientsErrors[$key] = ['ingredient' => 'Each ingredient must be an object/array'];
                    } elseif ((int) $data['item_id'] <= 0) {
                        $ingredientsErrors[$key] = 'Item ID must be a positive number';
                    } elseif (! Item::find($data['item_id'])) {
                        $ingredientsErrors[$key] = 'Item not found';
                    }
                }

                if (! empty($ingredientsErrors)) {
                    $errors['ingredients'] = $ingredientsErrors;
                }
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'name' => trim($data['name']),
            'date' => trim($data['date']),
            'ingredients' => $data['ingredients'] ?? [],
        ];
    }
}
