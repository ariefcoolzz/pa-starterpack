<?php
declare(strict_types=1);

namespace PA\Starter\Tests;

use PA\Starter\Administrator\Pure\Readiness;
use PHPUnit\Framework\TestCase;

final class ReadinessTest extends TestCase
{
    public function testMenghitungPlaceholder(): void
    {
        $html = '<a href="#" data-pa-kosong="1">x</a><a href="/a.pdf">2024</a><a href="#" data-pa-kosong="1">y</a>';
        $this->assertSame(2, Readiness::placeholders($html));
    }

    public function testHalamanTanpaPlaceholderDianggapSiap(): void
    {
        $this->assertTrue(Readiness::isReady('<h1>LKjIP</h1><a href="/images/dokumen/lkjip-2025.pdf">2025</a>'));
    }

    public function testHalamanDenganPlaceholderBelumSiap(): void
    {
        $this->assertFalse(Readiness::isReady('<a href="#" data-pa-kosong="1">Belum</a>'));
    }

    public function testNilaiRubrikSesuaiSK(): void
    {
        $this->assertSame(10, Readiness::score(true, 200));
        $this->assertSame(5, Readiness::score(false, 200));
        $this->assertSame(0, Readiness::score(false, 404));
    }
}
