<?php

namespace App\Requests\ItemsRequests;

use App\Exceptions\ValidationException;
use App\Models\Item;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoreItemsRequest implements RequestInterface
{
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

        if (empty($data['short_name'])) {
            $errors['short_name'] = 'Short name is required';
        } elseif (strlen($data['short_name']) > 50) {
            $errors['short_name'] = 'Short name must be less than 50 characters';
        } elseif (Item::where('short_name', $data['short_name'])->exists()) {
            $errors['short_name'] = 'Short name already exists';
        }

        if (! isset($data['balance'])) {
            $errors['balance'] = 'Balance is required';
        } elseif (! is_numeric($data['balance'])) {
            $errors['balance'] = 'Balance must be a number';
        } elseif ((float) $data['balance'] < 0) {
            $errors['balance'] = 'Balance must be positive';
        }

        if (empty($data['unit'])) {
            $errors['unit'] = 'Unit is required';
        } elseif (strlen($data['unit']) > 20) {
            $errors['unit'] = 'Unit must be less than 20 characters';
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'name' => trim($data['name']),
            'short_name' => trim($data['short_name']),
            'balance' => (float) $data['balance'],
            'unit' => trim($data['unit']),
        ];
    }
}
