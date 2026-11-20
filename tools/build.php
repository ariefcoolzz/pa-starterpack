<?php
declare(strict_types=1);

/**
 * Membangun paket ekstensi + quickstart.
 *
 *   php tools/build.php            # semua paket ekstensi
 *   php tools/build.php --docs     # + SINOVA-URL-MAP.csv / .md
 */

$root = dirname(__DIR__);
$dist = $root . '/dist';
@mkdir($dist, 0775, true);

$manifestSrc = $root . '/manifest';
$manifestDst = $root . '/packages/com_pastarter/admin/manifest';
@mkdir($manifestDst, 0775, true);

foreach (['indicators.json', 'sections.json', 'hero.html', 'shortcuts.html'] as $berkas) {
    copy($manifestSrc . '/' . $berkas, $manifestDst . '/' . $berkas);
}

/**
 * @return array{0:string,1:int,2:string}
 */
function zipDir(string $dir, string $zipPath, array $skip = []): array
{
    @unlink($zipPath);
    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        throw new RuntimeException("Tidak bisa membuat {$zipPath}");
    }

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1));

        foreach ($skip as $bad) {
            if (str_starts_with($rel, $bad)) {
                continue 2;
            }
        }

        $zip->addFile($file->getPathname(), $rel);
    }

    $zip->close();

    return [$zipPath, (int) filesize($zipPath), (string) hash_file('sha256', $zipPath)];
}

$targets = [
    'com_pastarter' => $root . '/packages/com_pastarter',
    'tpl_pa_satker' => $root . '/packages/tpl_pa_satker',
];

$hasil = [];
foreach ($targets as $nama => $dir) {
    $hasil[$nama] = zipDir($dir, $dist . '/' . $nama . '.zip');
}

printf("%-16s %10s  %s\n", 'paket', 'byte', 'sha256');
foreach ($hasil as $nama => [$path, $size, $hash]) {
    printf("%-16s %10d  %s\n", $nama, $size, $hash);
}

if (in_array('--docs', $argv, true)) {
    $rows = [];

    $ind = json_decode((string) file_get_contents($manifestSrc . '/indicators.json'), true, 512, JSON_THROW_ON_ERROR);
    $sec = json_decode((string) file_get_contents($manifestSrc . '/sections.json'), true, 512, JSON_THROW_ON_ERROR);

    $all = array_merge($ind['indicators'], $sec['extras']);

    foreach ($all as $row) {
        $path = $row['section'] === '_root'
            ? '/' . $row['path']
            : '/' . $row['section'] . '/' . $row['path'];

        $rows[] = [
            $row['id'],
            $row['aspect'] ?? 'tambahan',
            $row['no'] ?? '',
            $row['title'],
            $path,
            $row['criterion'] ?? '',
        ];
    }

    $fh = fopen($root . '/docs/SINOVA-URL-MAP.csv', 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, ['id', 'aspek', 'no', 'judul', 'url', 'kriteria']);
    foreach ($rows as $row) {
        fputcsv($fh, $row);
    }
    fclose($fh);

    echo "docs/SINOVA-URL-MAP.csv: " . count($rows) . " baris\n";
}
