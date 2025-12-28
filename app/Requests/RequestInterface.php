<?php

namespace App\Requests;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface
{
    public function validate(ServerRequestInterface|array $request): array;
}
