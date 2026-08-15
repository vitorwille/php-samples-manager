<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Repository\SampleRepositoryInterface;
use DateTime;

final class UpdateSampleStatus
{
    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    public function handle(string $sampleCode, SampleStatus $sampleStatus, ?DateTime $sampleConclusionDate = null): Sample
    {
        if ($sampleStatus === SampleStatus::CONCLUIDA && $sampleConclusionDate === null) {
            $sampleConclusionDate = new DateTime();
        }

        return $this->samples->updateSampleStatus($sampleCode, $sampleStatus, $sampleConclusionDate);
    }
}
