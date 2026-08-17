<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Sample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use App\Domain\Repository\SampleRepositoryInterface;
use DateTime;
use PDO;

final class MySqlSampleRepository implements SampleRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    /**
     * @return list<Sample>
     */
    public function findAllSamples(): array
    {
        $samples = [];
        foreach ($this->pdo->query('SELECT * FROM samples ORDER BY id') as $row) {
            $samples[] = $this->mapRow($row);
        }

        return $samples;
    }

    public function findBySampleCode(string $sampleCode): ?Sample
    {
        $stmt = $this->pdo->prepare('SELECT * FROM samples WHERE sample_code = ?');
        $stmt->execute([$sampleCode]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    /**
     * @return list<Sample>
     */
    public function searchBySampleCode(string $searchQuery): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM samples WHERE sample_code LIKE ? ORDER BY id');
        $stmt->execute(["%$searchQuery%"]);

        $samples = [];
        foreach ($stmt->fetchAll() as $row) {
            $samples[] = $this->mapRow($row);
        }

        return $samples;
    }

    /**
     * @return list<Sample>
     */
    public function findBySampleType(SampleType $sampleType): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM samples WHERE sample_type = ? ORDER BY id');
        $stmt->execute([$sampleType->value]);

        $samples = [];
        foreach ($stmt->fetchAll() as $row) {
            $samples[] = $this->mapRow($row);
        }

        return $samples;
    }

    public function createSample(string $sampleCode, SampleType $sampleType, SampleStatus $sampleStatus, ?string $sampleTechnician, DateTime $sampleReceivalDate, ?DateTime $sampleConclusionDate): Sample
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO samples (sample_code, sample_type, sample_status, sample_technician, sample_receival_date, sample_conclusion_date)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $sampleCode,
            $sampleType->value,
            $sampleStatus->value,
            $sampleTechnician,
            $sampleReceivalDate->format('Y-m-d'),
            $sampleConclusionDate?->format('Y-m-d'),
        ]);

        return new Sample(
            (int) $this->pdo->lastInsertId(),
            $sampleCode,
            $sampleType,
            $sampleStatus,
            $sampleTechnician,
            $sampleReceivalDate,
            $sampleConclusionDate,
        );
    }

    public function updateSampleStatus(string $sampleCode, SampleStatus $sampleStatus, ?DateTime $sampleConclusionDate = null): Sample
    {
        $stmt = $this->pdo->prepare(
            'UPDATE samples SET sample_status = ?, sample_conclusion_date = COALESCE(?, sample_conclusion_date) WHERE sample_code = ?'
        );
        $stmt->execute([
            $sampleStatus->value,
            $sampleConclusionDate?->format('Y-m-d H:i:s'),
            $sampleCode,
        ]);

        $sample = $this->findBySampleCode($sampleCode);

        if (!$sample) {
            throw new \InvalidArgumentException('Sample not found');
        }

        return $sample;
    }

    public function updateSampleTechnician(string $sampleCode, string $sampleTechnician): Sample
    {
        $stmt = $this->pdo->prepare('UPDATE samples SET sample_technician = ? WHERE sample_code = ?');
        $stmt->execute([$sampleTechnician, $sampleCode]);

        $sample = $this->findBySampleCode($sampleCode);

        if (!$sample) {
            throw new \InvalidArgumentException('Sample not found');
        }

        return $sample;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): Sample
    {
        return new Sample(
            (int) $row['id'],
            $row['sample_code'],
            SampleType::from($row['sample_type']),
            SampleStatus::from($row['sample_status']),
            $row['sample_technician'],
            new DateTime($row['sample_receival_date']),
            $row['sample_conclusion_date'] ? new DateTime($row['sample_conclusion_date']) : null,
        );
    }
}
