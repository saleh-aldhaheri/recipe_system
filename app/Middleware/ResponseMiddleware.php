<?php

namespace App\Middleware;

use App\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;
use Throwable;

class ResponseMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);

            return $response;
        } catch (ValidationException $e) {
            $response = new Response;
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], JSON_PRETTY_PRINT));

            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', 'application/json');

        } catch (HttpException $e) {
            $response = new Response;
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]), JSON_PRETTY_PRINT);

            return $response->withStatus($e->getCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (ModelNotFoundException $e) {
            $response = new Response;
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Resource not found',
            ], JSON_PRETTY_PRINT));

            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');

        } catch (Throwable $e) {

            $response = new Response;

            if (isAjaxRequest($request)) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'An error occurred',
                    'error' => $e->getMessage(),
                ], JSON_PRETTY_PRINT));

                return $response
                    ->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
            }

            $html = view('errors.500', ['error' => $e], 'main');
            $response->getBody()->write($html);

            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }
    }
}
