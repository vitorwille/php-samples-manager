<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use DateTime;

interface SampleRepositoryInterface
{
    /**
     * @return list<Sample>
     */
    public function findAllSamples(): array;

    public function findBySampleCode(string $sampleCode): ?Sample;

    /**
     * @return list<Sample>
     */
    public function searchBySampleCode(string $searchQuery): array;

    /**
     * @return list<Sample>
     */
    public function findBySampleType(SampleType $sampleType): array;

    public function createSample(string $sampleCode, SampleType $sampleType, SampleStatus $sampleStatus, string $sampleTechnician, DateTime $sampleReceivalDate, ?DateTime $sampleConclusionDate): Sample;

    public function updateSampleStatus(string $sampleCode, SampleStatus $sampleStatus, ?DateTime $sampleConclusionDate = null): Sample;
}
