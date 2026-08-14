<?php

use App\Application\UseCase\CreateUser;
use App\Application\UseCase\GetAllUsers;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Repository\MySqlUserRepository;
use Psr\Container\ContainerInterface;

return static function (ContainerInterface $container): void {
    $container->set(PDO::class, function (): PDO {
        $host = getenv('DB_HOST');
        $name = getenv('DB_NAME');
        $pdo = new PDO(
            "mysql:host=$host;dbname=$name;charset=utf8mb4",
            getenv('DB_USER'),
            getenv('DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )');

        return $pdo;
    });

    $container->set(UserRepositoryInterface::class, fn(ContainerInterface $c) => new MySqlUserRepository($c->get(PDO::class)));

    $container->set(GetAllUsers::class, fn(ContainerInterface $c) => new GetAllUsers($c->get(UserRepositoryInterface::class)));
    $container->set(CreateUser::class, fn(ContainerInterface $c) => new CreateUser($c->get(UserRepositoryInterface::class)));
    $container->set(UserController::class, fn(ContainerInterface $c) => new UserController($c->get(GetAllUsers::class), $c->get(CreateUser::class)));
};
