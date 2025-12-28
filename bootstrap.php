
<?php

use App\Middleware\ResponseMiddleware;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

require_once Core.'helper.php';

// Load .env file if it exists
if (file_exists(BASE.'.env')) {
    $envInstance = new Dotenv;
    $envInstance->load(BASE.'.env');
}

$app = AppFactory::create();

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => $_ENV['DB_DRIVER'],
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => $_ENV['DB_CHARSET'],
    'collation' => $_ENV['DB_COLLATION'],
    'prefix' => $_ENV['DB_PREFIX'] ?? '',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));

$capsule->setAsGlobal();

$capsule->bootEloquent();

// Add error middleware for exception handling
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Custom error handler for AJAX-friendly responses
$customErrorHandler = function (
    \Psr\Http\Message\ServerRequestInterface $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    // Handle ValidationException
    if ($exception instanceof \App\Exceptions\ValidationException) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => $exception->getErrors(),
        ], JSON_PRETTY_PRINT));

        return $response
            ->withStatus(422)
            ->withHeader('Content-Type', 'application/json');
    }

    // Handle ModelNotFoundException (Eloquent)
    if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Resource not found',
        ], JSON_PRETTY_PRINT));

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
    }

    // Handle QueryException (Database errors)
    if ($exception instanceof \Illuminate\Database\QueryException) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Database error occurred',
        ], JSON_PRETTY_PRINT));

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }

    // Default error response
    $response->getBody()->write(json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $displayErrorDetails ? $exception->getMessage() : 'Internal server error',
    ], JSON_PRETTY_PRINT));

    return $response
        ->withStatus(500)
        ->withHeader('Content-Type', 'application/json');
};

$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

require APP.'routes.php';

$app->add(ResponseMiddleware::class);
$app->run();
