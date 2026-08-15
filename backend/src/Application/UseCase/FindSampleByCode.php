<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Repository\SampleRepositoryInterface;

final class FindSampleByCode
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    public function handle(string $sampleCode): ?Sample
    {
        return $this->samples->findBySampleCode($sampleCode);
    }
}
