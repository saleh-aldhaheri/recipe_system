<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class UpdateRecipeRequest implements RequestInterface
{
    private int $recipeId;

    public function __construct(int $recipeId, private RequestInterface $storeIngredients)
    {
        $this->recipeId = $recipeId;
    }

    public function validate(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody() ?? [];
        $errors = [];

        if (isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'Name cannot be empty';
            } elseif (strlen($data['name']) < 3) {
                $errors['name'] = 'Name must be at least 3 characters';
            } elseif (strlen($data['name']) > 255) {
                $errors['name'] = 'Name must be less than 255 characters';
            }
        }

        if (isset($data['date'])) {
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

        $validated = [];

        if (isset($data['name'])) {
            $validated['name'] = trim($data['name']);
        }
        if (isset($data['date'])) {
            $validated['date'] = trim($data['date']);
        }
        if (isset($data['ingredients'])) {
            $validated['ingredients'] = $data['ingredients'];
        }

        return $validated;
    }
}
