<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\SampleStatus;
use PHPUnit\Framework\TestCase;

final class SampleStatusTest extends TestCase
{
    public function testValidValues(): void
    {
        $this->assertSame('recebida', SampleStatus::RECEBIDA->value);
        $this->assertSame('em_analise', SampleStatus::EM_ANALISE->value);
        $this->assertSame('concluida', SampleStatus::CONCLUIDA->value);
        $this->assertSame('rejeitada', SampleStatus::REJEITADA->value);
    }

    public function testFromAcceptsValidValues(): void
    {
        $this->assertSame(SampleStatus::RECEBIDA, SampleStatus::from('recebida'));
        $this->assertSame(SampleStatus::EM_ANALISE, SampleStatus::from('em_analise'));
        $this->assertSame(SampleStatus::CONCLUIDA, SampleStatus::from('concluida'));
        $this->assertSame(SampleStatus::REJEITADA, SampleStatus::from('rejeitada'));
    }

    public function testFromRejectsInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        SampleStatus::from('invalid_status');
    }

    public function testLabels(): void
    {
        $this->assertSame('Recebida', SampleStatus::RECEBIDA->label());
        $this->assertSame('Em Análise', SampleStatus::EM_ANALISE->label());
        $this->assertSame('Concluída', SampleStatus::CONCLUIDA->label());
        $this->assertSame('Rejeitada', SampleStatus::REJEITADA->label());
    }
}
