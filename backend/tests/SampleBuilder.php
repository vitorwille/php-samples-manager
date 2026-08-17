<?php

namespace App\Tests;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use DateTime;

final class SampleBuilder
{
    private int $id = 1;
    private string $sampleCode = 'LAB-001';
    private SampleType $sampleType = SampleType::AGUA;
    private SampleStatus $sampleStatus = SampleStatus::RECEBIDA;
    private ?string $sampleTechnician = 'Claudio';
    private DateTime $sampleReceivalDate;
    private ?DateTime $sampleConclusionDate = null;

    public function __construct()
    {
        $this->sampleReceivalDate = new DateTime('2026-08-14');
    }

    public function withCode(string $code): self
    {
        $this->sampleCode = $code;
        return $this;
    }

    public function withType(SampleType $type): self
    {
        $this->sampleType = $type;
        return $this;
    }

    public function withStatus(SampleStatus $status): self
    {
        $this->sampleStatus = $status;
        return $this;
    }

    public function withTechnician(?string $technician): self
    {
        $this->sampleTechnician = $technician;
        return $this;
    }

    public function withReceivalDate(string $date): self
    {
        $this->sampleReceivalDate = new DateTime($date);
        return $this;
    }

    public function withConclusionDate(?string $date): self
    {
        $this->sampleConclusionDate = $date ? new DateTime($date) : null;
        return $this;
    }

    public function withId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function build(): Sample
    {
        return new Sample(
            $this->id,
            $this->sampleCode,
            $this->sampleType,
            $this->sampleStatus,
            $this->sampleTechnician,
            $this->sampleReceivalDate,
            $this->sampleConclusionDate,
        );
    }
}
