<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class StoreRecipeRequest implements RequestInterface
{
    public function __construct(
        private RequestInterface $storeIngredients
    ) {}

    public function validate(ServerRequestInterface|array $request): array
    {
        $data = $request->getParsedBody() ?? [];
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
                    } else {
                        try {
                            $this->storeIngredients->validate($ingredient);
                        } catch (ValidationException $e) {
                            $ingredientsErrors[$key] = $e->getErrors();
                        } catch (Throwable $e) {
                            $ingredientsErrors[$key] = ['ingredient' => 'Invalid ingredient data'];
                        }
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
