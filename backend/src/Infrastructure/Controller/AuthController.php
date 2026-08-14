<?php

namespace App\Infrastructure\Controller;

use App\Application\UseCase\LoginUser;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuthController
{
    public function __construct(
        private readonly LoginUser $loginUser
    ) {}

    public function login(Request $request, Response $response): Response
    {
        $body = json_decode((string) $request->getBody(), true) ?? [];

        try {
            $user = $this->loginUser->handle(
                (string) ($body['email'] ?? ''),
                (string) ($body['password'] ?? ''),
            );
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));

            return $response->withStatus(401);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id();
        $_SESSION['name'] = $user->name();

        $response->getBody()->write(json_encode(['message' => 'Logged in successfully', 'logged_as' => $user->toArray()], JSON_PRETTY_PRINT));

        return $response;
    }

    public function logout(Request $request, Response $response): Response
    {
        $_SESSION = [];
        session_destroy();

        $response->getBody()->write(json_encode(['message' => 'Logged out'], JSON_PRETTY_PRINT));

        return $response;
    }
}
