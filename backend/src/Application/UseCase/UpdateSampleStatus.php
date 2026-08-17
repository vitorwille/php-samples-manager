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

        if (in_array($sample->sampleStatus(), [SampleStatus::CONCLUIDA, SampleStatus::REJEITADA], true)) {
            throw new \InvalidArgumentException('Sample is in a final status and cannot be changed.');
        }

        if ($sampleStatus === SampleStatus::EM_ANALISE) {  // deve ter um tecnico cadastrado
            if (trim($sample->sampleTechnician() ?? '') === '') {
                throw new \InvalidArgumentException('sampleTechnician is required to set status to em_analise');
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
