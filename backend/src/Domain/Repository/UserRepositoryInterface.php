<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepositoryInterface
{
    /**
     * @return list<User>
     */
    public function findAllUsers(): array;

    public function saveUser(string $name, string $email, string $password): User;
}
