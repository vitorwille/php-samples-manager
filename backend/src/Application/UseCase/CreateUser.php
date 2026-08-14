<?php

namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

final class CreateUser
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {}

    public function handle(string $name, string $email, string $password): User
    {
        if (!isset($name, $email, $password)) {
            throw new \InvalidArgumentException('Missing required fields: "name", "email", "password".');
        }

        if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[a-zA-Z]/', $password)) {
            throw new \InvalidArgumentException('Invalid password. Must contain more than 8 characters and at least a number and letter.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        return $this->users->saveUser(trim($name), strtolower($email), password_hash($password, PASSWORD_DEFAULT));
    }
}
