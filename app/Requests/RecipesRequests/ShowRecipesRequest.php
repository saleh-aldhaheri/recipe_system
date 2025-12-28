<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class ShowRecipesRequest implements RequestInterface
{
    public function __construct(
        private array $args
    ) {}

    public function validate(ServerRequestInterface $request): array
    {
        $errors = [];

        if (! isset($this->args['id'])) {
            $errors['id'] = 'ID is required';
        } else {
            $id = $this->args['id'];

            if (! is_numeric($id)) {
                $errors['id'] = 'ID must be a number';
            } elseif ((int) $id <= 0) {
                $errors['id'] = 'ID must be a positive number';
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'id' => (int) $this->args['id'],
        ];
    }
}
