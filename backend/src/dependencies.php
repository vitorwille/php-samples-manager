<?php

use App\Application\UseCase\CreateSample;
use App\Application\UseCase\CreateUser;
use App\Application\UseCase\FindSampleByCode;
use App\Application\UseCase\GetAllSamples;
use App\Application\UseCase\GetSamplesByType;
use App\Application\UseCase\GetAllUsers;
use App\Application\UseCase\LoginUser;
use App\Application\UseCase\SearchSamplesByCode;
use App\Application\UseCase\UpdateSampleStatus;
use App\Application\UseCase\UpdateSampleTechnician;
use App\Domain\Repository\SampleRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Controller\AuthController;
use App\Infrastructure\Controller\SampleController;
use App\Infrastructure\Controller\UserController;
use App\Infrastructure\Repository\MySqlSampleRepository;
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

        return $pdo;
    });

    $container->set(UserRepositoryInterface::class, fn(ContainerInterface $c) => new MySqlUserRepository($c->get(PDO::class)));
    $container->set(SampleRepositoryInterface::class, fn(ContainerInterface $c) => new MySqlSampleRepository($c->get(PDO::class)));

    $container->set(GetAllUsers::class, fn(ContainerInterface $c) => new GetAllUsers($c->get(UserRepositoryInterface::class)));
    $container->set(CreateUser::class, fn(ContainerInterface $c) => new CreateUser($c->get(UserRepositoryInterface::class)));
    $container->set(LoginUser::class, fn(ContainerInterface $c) => new LoginUser($c->get(UserRepositoryInterface::class)));

    $container->set(GetAllSamples::class, fn(ContainerInterface $c) => new GetAllSamples($c->get(SampleRepositoryInterface::class)));
    $container->set(GetSamplesByType::class, fn(ContainerInterface $c) => new GetSamplesByType($c->get(SampleRepositoryInterface::class)));
    $container->set(FindSampleByCode::class, fn(ContainerInterface $c) => new FindSampleByCode($c->get(SampleRepositoryInterface::class)));
    $container->set(SearchSamplesByCode::class, fn(ContainerInterface $c) => new SearchSamplesByCode($c->get(SampleRepositoryInterface::class)));
    $container->set(CreateSample::class, fn(ContainerInterface $c) => new CreateSample($c->get(SampleRepositoryInterface::class)));
    $container->set(UpdateSampleStatus::class, fn(ContainerInterface $c) => new UpdateSampleStatus($c->get(SampleRepositoryInterface::class)));
    $container->set(UpdateSampleTechnician::class, fn(ContainerInterface $c) => new UpdateSampleTechnician($c->get(SampleRepositoryInterface::class)));

    $container->set(AuthController::class, fn(ContainerInterface $c) => new AuthController($c->get(LoginUser::class)));
    $container->set(UserController::class, fn(ContainerInterface $c) => new UserController($c->get(GetAllUsers::class), $c->get(CreateUser::class)));
    $container->set(SampleController::class, fn(ContainerInterface $c) => new SampleController(
        $c->get(GetAllSamples::class),
        $c->get(GetSamplesByType::class),
        $c->get(FindSampleByCode::class),
        $c->get(SearchSamplesByCode::class),
        $c->get(CreateSample::class),
        $c->get(UpdateSampleStatus::class),
        $c->get(UpdateSampleTechnician::class),
    ));
};
