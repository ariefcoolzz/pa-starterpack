<?php
declare(strict_types=1);

namespace PA\Starter\Tests;

use PA\Starter\Administrator\Pure\IndicatorMap;
use PHPUnit\Framework\TestCase;

final class IndicatorMapTest extends TestCase
{
    private function map(): IndicatorMap
    {
        return IndicatorMap::fromFile(
            dirname(__DIR__) . '/manifest/indicators.json',
            dirname(__DIR__) . '/manifest/sections.json'
        );
    }

    public function testMemuatSeluruhIndikator(): void
    {
        $this->assertSame(35, $this->map()->count());
    }

    public function testJumlahPerAspekSesuaiLampiranII(): void
    {
        $this->assertCount(24, $this->map()->byAspect('berkala'));
        $this->assertCount(11, $this->map()->byAspect('wajib'));
    }

    public function testPathIndikatorUnik(): void
    {
        $paths = $this->map()->paths();

        $this->assertCount(35, $paths);
        $this->assertSame($paths, array_values(array_unique($paths)));
    }

    public function testHalamanTambahanPunyaPathSendiri(): void
    {
        $extras = $this->map()->extraPaths();

        $this->assertCount(6, $extras);
        $this->assertContains('/peta-sinova', $extras);
        $this->assertContains('/layanan-hukum/hak-pencari-keadilan', $extras);
    }

    public function testTidakAdaPathGandaAntaraIndikatorDanTambahan(): void
    {
        $all = array_merge($this->map()->paths(), $this->map()->extraPaths());

        $this->assertSame($all, array_values(array_unique($all)));
    }

    public function testMembentukUrlAbsolut(): void
    {
        $this->assertSame(
            'https://pa-contoh.go.id/transparansi/lkjip',
            $this->map()->url('berkala-3.11', 'https://pa-contoh.go.id/')
        );

        $this->assertSame(
            'https://pa-contoh.go.id/statistik-kunjungan',
            $this->map()->url('berkala-5.1', 'https://pa-contoh.go.id/')
        );
    }

    public function testNomorSkDariBerkas(): void
    {
        $this->assertSame('2722/DJA/SK.TI1/X/2026', $this->map()->sk());
    }

    public function testMenolakManifestTanpaIndikator(): void
    {
        $this->expectException(\RuntimeException::class);
        IndicatorMap::fromFile(__DIR__ . '/fixtures/indicators-mini.json');
    }
}
