<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Repository\SampleRepositoryInterface;

final class GetAllSamples
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    /** @return list<Sample> */
    public function handle(): array
    {
        return $this->samples->findAllSamples();
    }
}
