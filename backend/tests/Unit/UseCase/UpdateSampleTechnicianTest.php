<?php

namespace App\Tests\Unit\UseCase;

use App\Application\UseCase\UpdateSampleTechnician;
use App\Domain\Entity\SampleStatus;
use App\Domain\Repository\SampleRepositoryInterface;
use App\Tests\SampleBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class UpdateSampleTechnicianTest extends TestCase
{
    private SampleRepositoryInterface&MockObject $repo;
    private UpdateSampleTechnician $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(SampleRepositoryInterface::class);
        $this->useCase = new UpdateSampleTechnician($this->repo);
    }

    public function testThrowsWhenSampleNotFound(): void
    {
        $this->repo->method('findBySampleCode')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample not found');

        $this->useCase->handle('NONEXISTENT', 'Ana');
    }

    public function testThrowsWhenTechnicianIsEmpty(): void
    {
        $sample = (new SampleBuilder())->build();
        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: "sampleTechnician".');

        $this->useCase->handle('LAB-001', '');
    }

    public function testThrowsWhenTechnicianIsWhitespace(): void
    {
        $sample = (new SampleBuilder())->build();
        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: "sampleTechnician".');

        $this->useCase->handle('LAB-001', '   ');
    }

    public function testUpdatesTechnicianSuccessfully(): void
    {
        $sample = (new SampleBuilder())->withTechnician('Old Tech')->build();
        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this->repo->expects($this->once())
            ->method('updateSampleTechnician')
            ->with('LAB-001', 'New Tech')
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', 'New Tech');
    }
}
