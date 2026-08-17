<?php

namespace App\Tests\Unit\UseCase;

use App\Application\UseCase\CreateSample;
use App\Domain\Entity\SampleStatus;
use App\Domain\Entity\SampleType;
use App\Domain\Repository\SampleRepositoryInterface;
use App\Tests\SampleBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DateTime;

final class CreateSampleTest extends TestCase
{
    private SampleRepositoryInterface&MockObject $repo;
    private CreateSample $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(SampleRepositoryInterface::class);
        $this->useCase = new CreateSample($this->repo);
    }

    public function testGeneratesCodeWithPrefixYearAndSequencial(): void
    {
        $this->repo->method('getNextSequencial')->willReturn(1);
        $expected = (new SampleBuilder())->withCode('CANDXXX-2026-000001')->build();

        $this
            ->repo
            ->expects($this->once())
            ->method('createSample')
            ->with(
                'CANDXXX-2026-000001',
                SampleType::AGUA,
                SampleStatus::RECEBIDA,
                'Claudio',
                $this->callback(fn(DateTime $d) => $d->format('Y-m-d') === '2026-08-14'),
                null,
            )
            ->willReturn($expected);

        $result = $this->useCase->handle(
            SampleType::AGUA,
            SampleStatus::RECEBIDA,
            'Claudio',
            new DateTime('2026-08-14'),
            null,
        );

        $this->assertSame('CANDXXX-2026-000001', $result->sampleCode());
    }

    public function testPadsSequencialToSixDigits(): void
    {
        $this->repo->method('getNextSequencial')->willReturn(42);
        $expected = (new SampleBuilder())->withCode('CANDXXX-2026-000042')->build();

        $this
            ->repo
            ->expects($this->once())
            ->method('createSample')
            ->with('CANDXXX-2026-000042')
            ->willReturn($expected);

        $result = $this->useCase->handle(
            SampleType::AR,
            SampleStatus::RECEBIDA,
            'Marisa',
            new DateTime('2026-08-14'),
            null,
        );

        $this->assertSame('CANDXXX-2026-000042', $result->sampleCode());
    }

    public function testUsesReceivalDateYear(): void
    {
        $this->repo->method('getNextSequencial')->willReturn(1);
        $expected = (new SampleBuilder())->withCode('CANDXXX-2025-000001')->build();

        $this
            ->repo
            ->expects($this->once())
            ->method('getNextSequencial')
            ->with('CANDXXX', 2025)
            ->willReturn(1);

        $this->repo->method('createSample')->willReturn($expected);

        $result = $this->useCase->handle(
            SampleType::SOLO,
            SampleStatus::RECEBIDA,
            'Ana',
            new DateTime('2025-03-10'),
            null,
        );

        $this->assertSame('CANDXXX-2025-000001', $result->sampleCode());
    }

    public function testAllowsNullTechnician(): void
    {
        $this->repo->method('getNextSequencial')->willReturn(1);
        $expected = (new SampleBuilder())->withTechnician(null)->build();

        $this
            ->repo
            ->expects($this->once())
            ->method('createSample')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                null,
                $this->anything(),
                $this->anything(),
            )
            ->willReturn($expected);

        $result = $this->useCase->handle(
            SampleType::AGUA,
            SampleStatus::RECEBIDA,
            null,
            new DateTime('2026-08-14'),
            null,
        );

        $this->assertNull($result->sampleTechnician());
    }

    public function testAllowsConclusionDate(): void
    {
        $this->repo->method('getNextSequencial')->willReturn(1);
        $expected = (new SampleBuilder())->withConclusionDate('2026-08-20')->build();

        $this
            ->repo
            ->expects($this->once())
            ->method('createSample')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->callback(fn(DateTime $d) => $d->format('Y-m-d') === '2026-08-20'),
            )
            ->willReturn($expected);

        $result = $this->useCase->handle(
            SampleType::AGUA,
            SampleStatus::RECEBIDA,
            'Claudio',
            new DateTime('2026-08-14'),
            new DateTime('2026-08-20'),
        );

        $this->assertNotNull($result->sampleConclusionDate());
    }
}
