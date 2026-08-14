<?php

use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$container = new DI\Container();
AppFactory::setContainer($container);

(require __DIR__ . '/../src/dependencies.php')($container);

$app = AppFactory::create();

(require __DIR__ . '/../src/routes.php')($app);

$app->add(function ($request, $handler): ResponseInterface {
    return $handler->handle($request)->withHeader('Content-Type', 'application/json');
});

$app->run();
