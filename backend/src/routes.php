<?php

use App\Infrastructure\Controller\UserController;
use Slim\App;

return static function (App $app): void {
    $app->get('/api/users', [UserController::class, 'index']);
    $app->post('/api/users', [UserController::class, 'create']);
};
