<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PDO;

final class MySqlUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * @return list<User>
     */
    public function findAllUsers(): array
    {
        $users = [];
        foreach ($this->pdo->query('SELECT id, name, email FROM users ORDER BY id') as $row) {
            $users[] = new User((int) $row['id'], $row['name'], $row['email'], '');
        }

        return $users;
    }

    public function saveUser(string $name, string $email, string $password): User
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $password]);

        return new User((int) $this->pdo->lastInsertId(), $name, $email, $password);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row ? new User((int) $row['id'], $row['name'], $row['email'], $row['password']) : null;
    }
}
