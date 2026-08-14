<?php

use App\Infrastructure\Controller\AuthController;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Middleware\SessionAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

return static function (App $app): void {
    $app->post('/api/login', [AuthController::class, 'login']);
    $app->post('/api/logout', [AuthController::class, 'logout']);
    $app->post('/api/users', [UserController::class, 'create']);
    $app->get('/api/users', [UserController::class, 'index'])->add(SessionAuthMiddleware::class);

    $app->group('/api/samples', function (RouteCollectorProxy $group): void {
        //   $group->get('', [SampleController::class, 'index']);
        //  $group->post('', [SampleController::class, 'create']);

        // TODO: criar funcionalidade de amostras
    })->add(SessionAuthMiddleware::class);
};
