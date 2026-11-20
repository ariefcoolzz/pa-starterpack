<?php
declare(strict_types=1);

namespace PA\Starter\Pure;

final class Readiness
{
    public static function placeholders(string $html): int
    {
        return (int) preg_match_all('/data-pa-kosong\s*=\s*"1"/i', $html);
    }

    public static function isReady(string $html): bool
    {
        return self::placeholders($html) === 0;
    }

    /** Rubrik Lampiran II: 10 sempurna, 5 kurang lengkap, 0 tidak sesuai/tautan rusak. */
    public static function score(bool $ready, int $httpStatus): int
    {
        if ($httpStatus !== 200) {
            return 0;
        }

        return $ready ? 10 : 5;
    }
}
