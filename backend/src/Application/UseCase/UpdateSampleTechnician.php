<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Repository\SampleRepositoryInterface;

final class UpdateSampleTechnician
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    public function handle(string $sampleCode, string $sampleTechnician): Sample
    {
        $sample = $this->samples->findBySampleCode($sampleCode);

        if (!$sample) {
            throw new \InvalidArgumentException('Sample not found');
        }

        if (trim($sampleTechnician) === '') {
            throw new \InvalidArgumentException('Missing required field: "sampleTechnician".');
        }

        return $this->samples->updateSampleTechnician($sampleCode, $sampleTechnician);
    }
}
