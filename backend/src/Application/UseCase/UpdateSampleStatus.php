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
        $sample = $this->samples->findBySampleCode($sampleCode);

        if (!$sample) {
            throw new \InvalidArgumentException('Sample not found');
        }

        if ($sample->sampleStatus() === SampleStatus::REJEITADA) {
            throw new \InvalidArgumentException('Sample is already in "rejeitada" status.');
        }

        if ($sampleStatus === SampleStatus::EM_ANALISE) {  // deve ter um tecnico cadastrado
            if (trim($sample->sampleTechnician()) === '') {
                throw new \InvalidArgumentException('sampleTechnician is required to set status to em_analise');
            }
        }

        if ($sampleStatus === SampleStatus::CONCLUIDA || $sampleStatus === SampleStatus::REJEITADA) {
            if (in_array($sample->sampleStatus()->value, ['concluida', 'rejeitada'], true)) {
                throw new \InvalidArgumentException('Sample is already in "concluida" or "rejeitada" status');
            }
        }

        if ($sampleStatus === SampleStatus::CONCLUIDA) {
            if ($sample->sampleStatus()->value !== 'em_analise') {
                throw new \InvalidArgumentException('Sample must be in "em_analise" status to be set to "concluida".');
            }

            if ($sampleConclusionDate === null) {
                throw new \InvalidArgumentException('Sample must have a conclusion date to be set to "concluida".');
            }

            if ($sampleConclusionDate < $sample->sampleReceivalDate()) {
                throw new \InvalidArgumentException('Sample conclusion date must be equal or greater than sample receival date.');
            }
        }

        if ($sampleStatus === SampleStatus::REJEITADA) {
            if ($sample->sampleStatus()->value === 'concluida') {
                throw new \InvalidArgumentException('Sample cannot be rejected if it is already in "concluida" status.');
            }

            if ($sampleConclusionDate === null) {
                throw new \InvalidArgumentException('Sample must have a conclusion date to be set to "rejeitada".');
            }

            if ($sampleConclusionDate < $sample->sampleReceivalDate()) {
                throw new \InvalidArgumentException('Sample conclusion date must be equal or greater than sample receival date.');
            }
        }

        return $this->samples->updateSampleStatus($sampleCode, $sampleStatus, $sampleConclusionDate);
    }
}
