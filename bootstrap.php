
<?php

use App\Middleware\ResponseMiddleware;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Slim\Factory\AppFactory;
use Symfony\Component\Dotenv\Dotenv;

require_once Core.'helper.php';

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
//
$capsule->setEventDispatcher(new Dispatcher(new Container));

$capsule->setAsGlobal();

$capsule->bootEloquent();

require APP.'routes.php';

$app->add(ResponseMiddleware::class);
$app->run();
