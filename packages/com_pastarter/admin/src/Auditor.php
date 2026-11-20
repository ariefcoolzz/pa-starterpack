<?php
declare(strict_types=1);

namespace PA\Starter\Administrator;

use Joomla\CMS\Http\HttpFactory;
use PA\Starter\Administrator\Pure\IndicatorMap;
use PA\Starter\Administrator\Pure\Readiness;
use PA\Starter\Administrator\Pure\Scoring;
use Psr\Container\ContainerInterface;

defined('_JEXEC') or die;

/**
 * Memeriksa 35 URL indikator + butir desain/kecepatan.
 * Hasilnya estimasi mandiri, bukan nilai resmi SINOVA.
 */
final class Auditor
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /** @param array<int, string> $argv */
    public function run(string $base): array
    {
        $dir = getenv('PA_MANIFEST') && is_dir((string) getenv('PA_MANIFEST'))
            ? rtrim((string) getenv('PA_MANIFEST'), '/\\')
            : JPATH_ADMINISTRATOR . '/components/com_pastarter/manifest';

        $map = IndicatorMap::fromFile($dir . '/indicators.json', $dir . '/sections.json');

        $rows = [];
        foreach (array_merge($map->byAspect('berkala'), $map->byAspect('wajib')) as $row) {
            $rows[] = $this->check($map, $row, $base);
        }

        $design = $this->designScore($base);
        $infra  = $this->infraScore($base);

        return [
            'base'       => $base,
            'checked_at' => date('c'),
            'indicators' => $rows,
            'design'     => $design,
            'infra'      => $infra,
            'estimate'   => Scoring::estimate($rows, $design, $infra),
            'ready'      => count(array_filter($rows, static fn (array $r): bool => $r['score'] === 10)),
        ];
    }

    /**
     * @return array{0:int, 1:string, 2:float}
     */
    private function fetch(string $url): array
    {
        $mulai = microtime(true);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $body   = (string) curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return [$status, $body, microtime(true) - $mulai];
        }

        $ctx = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
        $body   = (string) @file_get_contents($url, false, $ctx);
        $status = 0;

        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#HTTP/\S+\s+(\d{3})#', $line, $m)) {
                $status = (int) $m[1];
            }
        }

        return [$status, $body, microtime(true) - $mulai];
    }

    /** @param array<string, mixed> $row */
    private function check(IndicatorMap $map, array $row, string $base): array
    {
        $url = $map->url($row['id'], $base);

        [$status, $html] = $this->fetch($url);

        $ready = $status === 200 && Readiness::isReady($html);

        return [
            'id'     => $row['id'],
            'aspect' => $row['aspect'],
            'title'  => $row['title'],
            'url'    => $url,
            'status' => $status,
            'kosong' => Readiness::placeholders($html),
            'ready'  => $ready,
            'score'  => Readiness::score($ready, $status),
        ];
    }

    private function designScore(string $base): float
    {
        [, $html] = $this->fetch(rtrim($base, '/') . '/');

        $poin  = str_contains($html, 'name="viewport"') ? 1 : 0;
        $poin += str_contains($html, 'pa-skip') ? 1 : 0;
        $poin += str_contains($html, 'pa-a11y') ? 1 : 0;
        $poin += str_contains($html, 'type="search"') ? 1 : 0;
        $poin += str_contains($html, 'aria-label') ? 1 : 0;

        return (float) ($poin * 2);
    }

    private function infraScore(string $base): float
    {
        [, , $detik] = $this->fetch(rtrim($base, '/') . '/');

        if ($detik === 0.0) {
            return 0.0;
        }

        // Kecepatan 7 poin, HTTPS 3 poin. Header keamanan diperiksa terpisah
        // oleh tools/verify.sh (curl -I).
        $poin  = $detik < 5 ? 7 : ($detik < 15 ? 4 : 0);
        $poin += str_starts_with($base, 'https://') ? 3 : 0;

        return (float) min(10, $poin);
    }
}
