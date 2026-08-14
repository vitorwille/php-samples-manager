<?php

namespace App\Domain\Entity;

final class User
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];
    }
}
