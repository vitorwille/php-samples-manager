<?php

namespace App\Infrastructure\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

final class SessionAuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        if (empty($_SESSION['user_id'])) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized'], JSON_PRETTY_PRINT));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request);
    }
}
