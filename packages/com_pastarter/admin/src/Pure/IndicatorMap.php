<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\Pure;

/**
 * Sumber kebenaran kontrak URL: 35 indikator + halaman tambahan.
 *
 * Satu indikator = satu path. Path gabungan '/<section>/<path>'; section '_root'
 * berarti '/<path>'.
 */
final class IndicatorMap
{
    /** @var array<string, array<string, mixed>> */
    private array $items = [];

    /** @var array<string, array<string, mixed>> */
    private array $extras = [];

    /** @param array<string, array<string, mixed>> $items */
    private function __construct(private readonly string $sk, array $items = [])
    {
        $this->items = $items;
    }

    public static function fromFile(string $path, ?string $sectionsPath = null): self
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Manifest tidak ditemukan: {$path}");
        }

        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach (['version', 'sk', 'indicators'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \RuntimeException("Manifest tanpa kunci '{$key}'");
            }
        }

        if ($data['indicators'] === []) {
            throw new \RuntimeException('Manifest tanpa indikator');
        }

        $map = new self((string) $data['sk']);

        foreach ($data['indicators'] as $row) {
            foreach (['id', 'aspect', 'title', 'section', 'path', 'criterion'] as $key) {
                if (!isset($row[$key]) || $row[$key] === '') {
                    throw new \RuntimeException("Indikator tanpa '{$key}': " . json_encode($row));
                }
            }

            if (!in_array($row['aspect'], ['berkala', 'wajib'], true)) {
                throw new \RuntimeException("Aspek tidak dikenal: {$row['aspect']}");
            }

            if (isset($map->items[$row['id']])) {
                throw new \RuntimeException("ID ganda: {$row['id']}");
            }

            $map->items[$row['id']] = $row;
        }

        if ($sectionsPath !== null) {
            if (!is_file($sectionsPath)) {
                throw new \RuntimeException("Manifest seksi tidak ditemukan: {$sectionsPath}");
            }

            $sections = json_decode((string) file_get_contents($sectionsPath), true, 512, JSON_THROW_ON_ERROR);

            foreach ($sections['extras'] ?? [] as $extra) {
                $map->extras[$extra['id']] = $extra;
            }
        }

        return $map;
    }

    public function sk(): string
    {
        return $this->sk;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @return array<int, array<string, mixed>> */
    public function byAspect(string $aspect): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (array $row): bool => $row['aspect'] === $aspect
        ));
    }

    /** @return array<int, string> */
    public function paths(): array
    {
        return array_values(array_map(
            static fn (array $row): string => self::pathOf($row),
            $this->items
        ));
    }

    /** @return array<string, array<string, mixed>> */
    public function extras(): array
    {
        return $this->extras;
    }

    /** @return array<int, string> */
    public function extraPaths(): array
    {
        return array_values(array_map(
            static fn (array $row): string => self::pathOf($row),
            $this->extras
        ));
    }

    public function url(string $id, string $baseUrl): string
    {
        $row = $this->items[$id] ?? $this->extras[$id] ?? throw new \RuntimeException("ID tidak ada: {$id}");

        return rtrim($baseUrl, '/') . self::pathOf($row);
    }

    /** @param array<string, mixed> $row */
    public static function pathOf(array $row): string
    {
        return $row['section'] === '_root'
            ? '/' . $row['path']
            : '/' . $row['section'] . '/' . $row['path'];
    }
}
