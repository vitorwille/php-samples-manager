<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use App\Domain\Repository\SampleRepositoryInterface;
use DateTime;

final class CreateSample
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    public function handle(string $sampleCode, SampleType $sampleType, SampleStatus $sampleStatus, string $sampleTechnician, DateTime $sampleReceivalDate, ?DateTime $sampleConclusionDate): Sample
    {
        if (trim($sampleCode) === '') {
            throw new \InvalidArgumentException('Missing required field: "sampleCode".');
        }

        return $this->samples->createSample(
            trim($sampleCode),
            $sampleType,
            $sampleStatus,
            $sampleTechnician,
            $sampleReceivalDate,
            $sampleConclusionDate,
        );
    }
}
