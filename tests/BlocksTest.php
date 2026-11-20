<?php
declare(strict_types=1);

namespace PA\Starter\Tests;

use PA\Starter\Administrator\Pure\Blocks;
use PHPUnit\Framework\TestCase;

final class BlocksTest extends TestCase
{
    private function render(array $blocks, array $tokens = [], array $pre = []): string
    {
        return Blocks::render($blocks, $tokens, $pre);
    }

    public function testTokenDigantiDiSeluruhTeks(): void
    {
        $html = $this->render(
            [['t' => 'intro', 'text' => 'Pengadilan Agama {{SITE_NAME}} tahun {{TAHUN}}']],
            ['{{SITE_NAME}}' => 'Contoh', '{{TAHUN}}' => '2026']
        );

        $this->assertStringContainsString('Pengadilan Agama Contoh tahun 2026', $html);
    }

    public function testSelPlaceholderMenjadiTautanBertanda(): void
    {
        $html = $this->render([[
            't' => 'table',
            'head' => ['Tahun', 'Tautan'],
            'rows' => [['2024', '!Belum diunggah']],
        ]]);

        $this->assertStringContainsString('href="#"', $html);
        $this->assertStringContainsString('data-pa-kosong="1"', $html);
        $this->assertStringContainsString('>Belum diunggah</a>', $html);
    }

    public function testBlokFilesMembuatTabelLimaTahun(): void
    {
        $html = $this->render([[
            't' => 'files',
            'years' => [2026, 2025, 2024, 2023, 2022],
            'doc' => 'LKjIP',
        ]]);

        $this->assertSame(5, substr_count($html, 'data-pa-kosong="1"'));
        $this->assertStringContainsString('2022', $html);
        $this->assertStringContainsString('LKjIP', $html);
    }

    public function testTeksDiEscapeSebelumDitampilkan(): void
    {
        $html = $this->render([['t' => 'intro', 'text' => '<script>alert(1)</script>']]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testPlaceholderSeBarisDalamTeksLangkah(): void
    {
        $html = $this->render([[
            't' => 'steps',
            'items' => [['title' => 'Banding', 'text' => 'Tautan berkas: !Belum diunggah']],
        ]]);

        $this->assertStringContainsString('data-pa-kosong="1"', $html);
        $this->assertStringContainsString('>Belum diunggah</a>', $html);
    }

    public function testBlokModuleMenjadiLoadPosition(): void
    {
        $html = $this->render([['t' => 'module', 'position' => 'statistik-kunjungan']]);

        $this->assertStringContainsString('{loadposition statistik-kunjungan}', $html);
    }

    public function testBlokSinovaMemakaiHtmlPracetak(): void
    {
        $html = $this->render([['t' => 'sinova-table']], [], ['sinova-table' => '<table id="sinova"></table>']);

        $this->assertStringContainsString('id="sinova"', $html);
    }
}
