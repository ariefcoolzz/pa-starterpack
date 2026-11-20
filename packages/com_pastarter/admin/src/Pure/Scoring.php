<?php
declare(strict_types=1);

namespace PA\Starter\Pure;

final class Scoring
{
    /** Bobot Lampiran II: berkala 40, wajib 20, desain 20, infrastruktur 20. */
    public const WEIGHTS = ['berkala' => 40.0, 'wajib' => 20.0];

    /**
     * @param array<int, array{aspect: string, score: int}> $rows
     * @return array{content: float, design: float, infra: float, total: float}
     */
    public static function estimate(array $rows, float $design, float $infra): array
    {
        $content = 0.0;

        foreach (self::WEIGHTS as $aspect => $weight) {
            $items = array_values(array_filter($rows, static fn (array $r): bool => $r['aspect'] === $aspect));
            $max   = count($items) * 10;

            if ($max === 0) {
                continue;
            }

            $got      = array_sum(array_column($items, 'score'));
            $content += ($got / $max) * $weight;
        }

        $designPart = ($design / 10) * 20.0;
        $infraPart  = ($infra / 10) * 20.0;

        return [
            'content' => round($content, 2),
            'design'  => round($designPart, 2),
            'infra'   => round($infraPart, 2),
            'total'   => round($content + $designPart + $infraPart, 2),
        ];
    }
}
