<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class InputRecipesRequest implements RequestInterface
{
    public function validate(ServerRequestInterface|array $request): array
    {
        $files = $request->getUploadedFiles();
        $errors = [];

        $allowedMimeTypes = [
            'text/csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $maxSize = 5 * 1024 * 1024;

        foreach ($files as $key => $file) {

            if ($file->getError() !== UPLOAD_ERR_OK) {
                $errors[$key]['upload'] = 'File upload failed.';

                continue;
            }

            if (! in_array($file->getClientMediaType(), $allowedMimeTypes)) {
                $errors[$key]['format'] = 'Invalid file format. Only CSV or XLSX files are allowed.';
            }

            if ($file->getSize() > $maxSize) {
                $errors[$key]['size'] = 'File size must not exceed 5MB.';
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'files' => $files,
        ];
    }
}
