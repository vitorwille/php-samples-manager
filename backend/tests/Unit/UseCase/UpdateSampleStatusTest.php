<?php

namespace App\Tests\Unit\UseCase;

use App\Application\UseCase\UpdateSampleStatus;
use App\Domain\Entity\SampleStatus;
use App\Domain\Repository\SampleRepositoryInterface;
use App\Tests\SampleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DateTime;

final class UpdateSampleStatusTest extends TestCase
{
    private SampleRepositoryInterface&MockObject $repo;
    private UpdateSampleStatus $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(SampleRepositoryInterface::class);
        $this->useCase = new UpdateSampleStatus($this->repo);
    }

    public function testThrowsWhenSampleNotFound(): void
    {
        $this->repo->method('findBySampleCode')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample not found');

        $this->useCase->handle('NONEXISTENT', SampleStatus::EM_ANALISE);
    }

    // em analise

    public function testEmAnaliseRequiresTechnician(): void
    {
        $sample = (new SampleBuilder())
            ->withTechnician(null)
            ->withStatus(SampleStatus::RECEBIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sampleTechnician is required to set status to em_analise');

        $this->useCase->handle('LAB-001', SampleStatus::EM_ANALISE);
    }

    public function testEmAnaliseRequiresNonEmptyTechnician(): void
    {
        $sample = (new SampleBuilder())
            ->withTechnician('   ')
            ->withStatus(SampleStatus::RECEBIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('sampleTechnician is required to set status to em_analise');

        $this->useCase->handle('LAB-001', SampleStatus::EM_ANALISE);
    }

    public function testEmAnaliseSucceedsWithTechnician(): void
    {
        $sample = (new SampleBuilder())
            ->withTechnician('Claudio')
            ->withStatus(SampleStatus::RECEBIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this
            ->repo
            ->expects($this->once())
            ->method('updateSampleStatus')
            ->with('LAB-001', SampleStatus::EM_ANALISE, null)
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', SampleStatus::EM_ANALISE);
    }

    // alterar status apos rejeitada

    public function testCannotChangeStatusFromRejeitada(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::REJEITADA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::EM_ANALISE);
    }

    public function testRejeitadaCannotBeSetFromRejeitada(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::REJEITADA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, new DateTime('2026-08-20'));
    }

    // concluida

    public function testConcluidaRequiresEmAnaliseStatus(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::RECEBIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample must be in "em_analise" status to be set to "concluida".');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-20'));
    }

    public function testConcluidaRequiresEmAnaliseNotRejeitada(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::REJEITADA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-20'));
    }

    public function testConcluidaRequiresConclusionDate(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample must have a conclusion date to be set to "concluida".');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, null);
    }

    public function testConcluidaRejectsDateBeforeReceival(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->withReceivalDate('2026-08-15')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample conclusion date must be equal or greater than sample receival date.');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-10'));
    }

    public function testConcluidaAcceptsDateEqualToReceival(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->withReceivalDate('2026-08-15')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this
            ->repo
            ->expects($this->once())
            ->method('updateSampleStatus')
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-15'));
    }

    public function testConcluidaSucceedsWithValidData(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->withReceivalDate('2026-08-14')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this
            ->repo
            ->expects($this->once())
            ->method('updateSampleStatus')
            ->with('LAB-001', SampleStatus::CONCLUIDA, $this->callback(fn(DateTime $d) => $d->format('Y-m-d') === '2026-08-20'))
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-20'));
    }

    public function testConcluidaCannotBeSetFromConcluida(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::CONCLUIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-20'));
    }

    public function testConcluidaCannotTransitionToEmAnalise(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::CONCLUIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::EM_ANALISE);
    }

    public function testRejeitadaCannotTransitionToConcluida(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::REJEITADA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::CONCLUIDA, new DateTime('2026-08-20'));
    }

    // rejeitada

    public function testRejeitadaCannotRejectConcluida(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::CONCLUIDA)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample is in a final status and cannot be changed.');

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, new DateTime('2026-08-20'));
    }

    public function testRejeitadaRequiresConclusionDate(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample must have a conclusion date to be set to "rejeitada".');

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, null);
    }

    public function testRejeitadaRejectsDateBeforeReceival(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->withReceivalDate('2026-08-15')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample conclusion date must be equal or greater than sample receival date.');

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, new DateTime('2026-08-10'));
    }

    public function testRejeitadaSucceedsWithValidData(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::EM_ANALISE)
            ->withReceivalDate('2026-08-14')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this
            ->repo
            ->expects($this->once())
            ->method('updateSampleStatus')
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, new DateTime('2026-08-20'));
    }

    public function testRejeitadaSucceedsFromRecebida(): void
    {
        $sample = (new SampleBuilder())
            ->withStatus(SampleStatus::RECEBIDA)
            ->withTechnician('Marisa')
            ->withReceivalDate('2026-08-14')
            ->build();

        $this->repo->method('findBySampleCode')->willReturn($sample);
        $this
            ->repo
            ->expects($this->once())
            ->method('updateSampleStatus')
            ->willReturn($sample);

        $this->useCase->handle('LAB-001', SampleStatus::REJEITADA, new DateTime('2026-08-16'));
    }
}
