<?php

namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

final class LoginUser
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {}

    public function handle(string $email, string $password): User
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));

        if (!$user || !password_verify($password, $user->password())) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        return $user;
    }
}
