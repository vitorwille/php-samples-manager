<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use App\Domain\Repository\SampleRepositoryInterface;
use DateTime;

final class CreateSample
{
    private const PREFIX = 'CANDXXX';

    public function __construct(
        private readonly SampleRepositoryInterface $samples
    ) {}

    public function handle(SampleType $sampleType, SampleStatus $sampleStatus, ?string $sampleTechnician, DateTime $sampleReceivalDate, ?DateTime $sampleConclusionDate): Sample
    {
        $year = (int) $sampleReceivalDate->format('Y');
        $sequencial = $this->samples->getNextSequencial(self::PREFIX, $year);
        $sampleCode = self::PREFIX . '-' . $year . '-' . str_pad((string) $sequencial, 6, '0', STR_PAD_LEFT);

        return $this->samples->createSample(
            $sampleCode,
            $sampleType,
            $sampleStatus,
            $sampleTechnician,
            $sampleReceivalDate,
            $sampleConclusionDate,
        );
    }
}
