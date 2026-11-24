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

    public function testSemuaAliasManifestUnikGlobal(): void
    {
        $root = dirname(__DIR__);
        $ind  = json_decode((string) file_get_contents($root . '/manifest/indicators.json'), true, 512, JSON_THROW_ON_ERROR);
        $sec  = json_decode((string) file_get_contents($root . '/manifest/sections.json'), true, 512, JSON_THROW_ON_ERROR);

        $aliases = array_merge(
            array_column($sec['sections'] ?? [], 'alias'),
            array_column($ind['indicators'] ?? [], 'path'),
            array_column($sec['extras'] ?? [], 'path')
        );

        $this->assertCount(count($aliases), array_unique($aliases));
    }

    public function testBerkasKontenModulTersedia(): void
    {
        $manifestDir = dirname(__DIR__) . '/manifest';
        $modules     = json_decode((string) file_get_contents($manifestDir . '/modules.json'), true, 512, JSON_THROW_ON_ERROR);

        $checked = 0;
        foreach ($modules['modules'] ?? [] as $mod) {
            if (!empty($mod['content'])) {
                $this->assertFileExists($manifestDir . '/' . $mod['content']);
                $checked++;
            }
        }

        $this->assertGreaterThan(0, $checked);
    }

    public function testBerkasRilisDanAdminUtamaTersedia(): void
    {
        $root = dirname(__DIR__);

        $this->assertFileExists($root . '/packages/pkg_pastarter/pkg_pastarter.xml');
        $this->assertFileExists($root . '/packages/com_pastarter/script.php');
        $this->assertFileExists($root . '/packages/com_pastarter/admin/src/Controller/DisplayController.php');
        $this->assertFileExists($root . '/docs/PANDUAN-INSTALASI-EKSTENSI.txt');
        $this->assertFileExists($root . '/docs/PANDUAN-INSTALASI-QUICKSTART.txt');
    }
}
