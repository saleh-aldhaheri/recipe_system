<?php

namespace App\Requests\RecipesRequests;

use App\Exceptions\ValidationException;
use App\Requests\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class InputRecipesRequest implements RequestInterface
{
    public function validate(ServerRequestInterface|array $request): array
    {
        if (! $request instanceof ServerRequestInterface) {
            throw new ValidationException(['files' => 'Invalid request']);
        }

        $uploadedFiles = $request->getUploadedFiles();
        $errors = [];

        if (empty($uploadedFiles)) {
            $errors['files'] = 'No files uploaded';
        } else {
            $validFiles = [];

            foreach ($uploadedFiles as $key => $file) {
                if (is_array($file)) {
                    foreach ($file as $subFile) {
                        if ($subFile instanceof UploadedFileInterface) {
                            $fileError = $this->validateFile($subFile);
                            if ($fileError) {
                                $errors['files'][$key][] = $fileError;
                            } else {
                                $validFiles[] = $subFile;
                            }
                        }
                    }
                } elseif ($file instanceof UploadedFileInterface) {
                    $fileError = $this->validateFile($file);
                    if ($fileError) {
                        $errors['files'][$key] = $fileError;
                    } else {
                        $validFiles[] = $file;
                    }
                }
            }

            if (empty($validFiles)) {
                $errors['files'] = 'No valid files uploaded';
            }
        }

        if (! empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'files' => $this->normalizeFiles($uploadedFiles),
        ];
    }

    private function validateFile(UploadedFileInterface $file): ?string
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return 'File upload error: '.$this->getUploadErrorMessage($file->getError());
        }

        $clientFilename = $file->getClientFilename();
        if (empty($clientFilename)) {
            return 'File name is required';
        }

        $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        $allowedExtensions = ['xlsx', 'xls'];

        if (! in_array($extension, $allowedExtensions)) {
            return 'Invalid file type. Only Excel files (.xlsx, .xls) are allowed';
        }

        $size = $file->getSize();
        if ($size === null || $size > 10 * 1024 * 1024) { // 10MB limit
            return 'File size exceeds 10MB limit';
        }

        return null;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error',
        };
    }

    private function normalizeFiles(array $uploadedFiles): array
    {
        $normalized = [];

        foreach ($uploadedFiles as $key => $file) {
            if (is_array($file)) {
                $normalized[$key] = [];
                foreach ($file as $subFile) {
                    if ($subFile instanceof UploadedFileInterface) {
                        $normalized[$key][] = $subFile;
                    }
                }
            } elseif ($file instanceof UploadedFileInterface) {
                $normalized[$key] = $file;
            }
        }

        return $normalized;
    }
}
