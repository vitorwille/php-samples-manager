<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\SampleType;
use PHPUnit\Framework\TestCase;

final class SampleTypeTest extends TestCase
{
    public function testValidValues(): void
    {
        $this->assertSame('agua', SampleType::AGUA->value);
        $this->assertSame('ar', SampleType::AR->value);
        $this->assertSame('efluente', SampleType::EFLUENTE->value);
        $this->assertSame('solo', SampleType::SOLO->value);
    }

    public function testFromAcceptsValidValues(): void
    {
        $this->assertSame(SampleType::AGUA, SampleType::from('agua'));
        $this->assertSame(SampleType::AR, SampleType::from('ar'));
        $this->assertSame(SampleType::EFLUENTE, SampleType::from('efluente'));
        $this->assertSame(SampleType::SOLO, SampleType::from('solo'));
    }

    public function testFromRejectsInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        SampleType::from('invalid_type');
    }

    public function testLabels(): void
    {
        $this->assertSame('Água', SampleType::AGUA->label());
        $this->assertSame('Ar', SampleType::AR->label());
        $this->assertSame('Efluente', SampleType::EFLUENTE->label());
        $this->assertSame('Solo', SampleType::SOLO->label());
    }
}
