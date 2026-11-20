<?php
declare(strict_types=1);

namespace PA\Starter\Tests;

use PA\Starter\Pure\Scoring;
use PHPUnit\Framework\TestCase;

final class ScoringTest extends TestCase
{
    /** @return array<int, array{aspect: string, score: int}> */
    private function rows(int $berkala, int $wajib, int $value): array
    {
        $rows = [];
        for ($i = 0; $i < 24; $i++) {
            $rows[] = ['aspect' => 'berkala', 'score' => $berkala];
        }
        for ($i = 0; $i < 11; $i++) {
            $rows[] = ['aspect' => 'wajib', 'score' => $wajib];
        }

        return $rows;
    }

    public function testSemuaSempurnaMenghasilkanSeratus(): void
    {
        $this->assertSame(100.0, Scoring::estimate($this->rows(10, 10, 0), 10, 10)['total']);
    }

    public function testKontenKosongHanyaMenyisakanDesainDanInfrastruktur(): void
    {
        $this->assertSame(40.0, Scoring::estimate($this->rows(0, 0, 0), 10, 10)['total']);
    }

    public function testNilaiSetengahMemberiBobotProporsional(): void
    {
        $this->assertSame(70.0, Scoring::estimate($this->rows(5, 5, 0), 10, 10)['total']);
    }

    public function testRincianAspekDikembalikan(): void
    {
        $result = Scoring::estimate($this->rows(10, 0, 0), 0, 0);

        $this->assertSame(40.0, $result['content']);
        $this->assertSame(0.0, $result['design']);
        $this->assertSame(0.0, $result['infra']);
    }
}
