<?php

namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

final class GetAllUsers
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {}

    /**
     * @return list<User>
     */
    public function handle(): array
    {
        return $this->users->findAllUsers();
    }
}
