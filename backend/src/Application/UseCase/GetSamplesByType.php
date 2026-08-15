<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleType;
use App\Domain\Repository\SampleRepositoryInterface;

final class GetSamplesByType
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    /** @return list<Sample> */
    public function handle(SampleType $sampleType): array
    {
        return $this->samples->findBySampleType($sampleType);
    }
}
