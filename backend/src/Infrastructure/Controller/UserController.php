<?php

namespace App\Infrastructure\Controller;

use App\Application\UseCase\CreateUser;
use App\Application\UseCase\GetAllUsers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserController
{
    public function __construct(
        private readonly GetAllUsers $getAllUsers,
        private readonly CreateUser $createUser,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $users = [];
        foreach ($this->getAllUsers->handle() as $user) {
            $users[] = $user->toArray();
        }

        $response->getBody()->write(json_encode($users, JSON_PRETTY_PRINT));
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $body = json_decode((string) $request->getBody(), true) ?? [];

        try {
            $user = $this->createUser->handle(
                (string) ($body['name'] ?? ''),
                (string) ($body['email'] ?? ''),
                (string) ($body['password'] ?? ''),
            );
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));

            return $response->withStatus(400);
        }

        $response->getBody()->write(json_encode($user->toArray(), JSON_PRETTY_PRINT));

        return $response->withStatus(201);
    }

    public function verifyUser(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode(['status' => 'Authenticated'], JSON_PRETTY_PRINT));

        return $response->withStatus(200);
    }
}
