<?php

namespace App\Requests\ItemsRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class ShowItemsRequest implements RequestInterface
{
    private array $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function validate(ServerRequestInterface $request): array
    {
        $id = $this->args['id'] ?? null;
        $errors = [];

        if (empty($id)) {
            $errors['id'] = 'ID is required';
        } elseif (! is_numeric($id)) {
            $errors['id'] = 'ID must be a number';
        } elseif ((int) $id <= 0) {
            $errors['id'] = 'ID must be a positive number';
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'id' => (int) $id,
        ];
    }
}
