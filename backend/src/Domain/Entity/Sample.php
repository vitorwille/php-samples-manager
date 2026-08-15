<?php

namespace App\Domain\Entity;

use DateTime;

final class Sample
{
    public function __construct(
        private readonly int $id,
        private readonly string $sampleCode,
        private readonly SampleType $sampleType,
        private readonly SampleStatus $sampleStatus,  // enum
        private readonly string $sampleTechnician,
        private readonly DateTime $sampleReceivalDate,
        private readonly ?DateTime $sampleConclusionDate,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function sampleCode(): string
    {
        return $this->sampleCode;
    }

    public function sampleType(): SampleType
    {
        return $this->sampleType;
    }

    public function sampleStatus(): SampleStatus
    {
        return $this->sampleStatus;
    }

    public function sampleTechnician(): string
    {
        return $this->sampleTechnician;
    }

    public function sampleReceivalDate(): DateTime
    {
        return $this->sampleReceivalDate;
    }

    public function sampleConclusionDate(): ?DateTime
    {
        return $this->sampleConclusionDate;
    }

    /**
     * @return array{id: int, sampleCode: string, sampleType: string, sampleStatus: string, sampleTechnician: string, sampleReceivalDate: string, sampleConclusionDate: ?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sampleCode' => $this->sampleCode,
            'sampleType' => $this->sampleType->value,
            'sampleStatus' => $this->sampleStatus->value,
            'sampleTechnician' => $this->sampleTechnician,
            'sampleReceivalDate' => $this->sampleReceivalDate->format('Y-m-d'),
            'sampleConclusionDate' => $this->sampleConclusionDate?->format('Y-m-d'),
        ];
    }
}
