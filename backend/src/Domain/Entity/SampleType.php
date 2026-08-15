<?php

namespace App\Domain\Entity;

enum SampleType: string
{
    case AGUA = 'agua';
    case AR = 'ar';
    case EFLUENTE = 'efluente';
    case SOLO = 'solo';

    public function label(): string
    {
        return match ($this) {
            self::AGUA => 'Água',
            self::AR => 'Ar',
            self::EFLUENTE => 'Efluente',
            self::SOLO => 'Solo',
        };
    }
}
