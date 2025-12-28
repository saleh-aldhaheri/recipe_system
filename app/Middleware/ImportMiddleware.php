<?php 

namespace App\Middleware;

use App\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;

class ImportMiddleware implements  MiddlewareInterface 
{ 
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $files = $request->getUploadedFiles();  

        if(empty($files)){ 
          throw new HttpBadRequestException($request); 
        } 

        if(count($files) > 7) { 
          throw new ValidationException(['limit' => 'exceeded the allowed limit']); 
        }

        return $handler->handle($request); 
    }
}