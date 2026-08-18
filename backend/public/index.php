<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

session_start();
register_shutdown_function('session_write_close');

$container = new DI\Container();
AppFactory::setContainer($container);

(require __DIR__ . '/../src/dependencies.php')($container);

$app = AppFactory::create();

(require __DIR__ . '/../src/routes.php')($app);

$app->add(function (Request $request, $handler) use ($app): ResponseInterface {
    if ($request->getMethod() === 'OPTIONS') {
        return $app
            ->getResponseFactory()
            ->createResponse(204)
            ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS');
    }

    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Content-Type', 'application/json');
});

$errorMiddleware = $app->addErrorMiddleware(false, false, false);
$errorMiddleware->setDefaultErrorHandler(function (Request $request, Throwable $e) use ($app): ResponseInterface {
    $status = $e instanceof HttpException ? $e->getCode() : 500;
    $response = $app->getResponseFactory()->createResponse($status);
    if ($e instanceof HttpException) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));
    } else {
        $response->getBody()->write(json_encode(['error' => 'Unknown error'], JSON_PRETTY_PRINT));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
