<?php
declare(strict_types=1);

/**
 * Membangun paket ekstensi, pkg_pastarter.zip, dan arsip rilis ekstensi siap unduh.
 *
 *   php tools/build.php                   # 5 ekstensi + pkg_pastarter.zip
 *   php tools/build.php --docs            # + docs/SINOVA-URL-MAP.csv
 *   php tools/build.php --docs --release  # + dist/pa-starterpack-ekstensi-v1.0.0.zip
 */

const PA_RELEASE_VERSION = '1.0.0';

$root = dirname(__DIR__);
$dist = $root . '/dist';
@mkdir($dist, 0775, true);

/**
 * Menghapus isi direktori secara rekursif tanpa error bila belum ada.
 */
function rmTree(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($it as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($dir);
}

/**
 * Menyalin direktori secara rekursif sambil mengabaikan berkas tersembunyi OS (.DS_Store).
 */
function copyTree(string $src, string $dst): void
{
    if (!is_dir($src)) {
        return;
    }

    @mkdir($dst, 0775, true);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $item) {
        $name = $item->getFilename();
        if ($name === '' || $name[0] === '.' || $name === 'Thumbs.db') {
            continue;
        }

        $rel    = substr($item->getPathname(), strlen($src) + 1);
        $target = $dst . '/' . $rel;

        if ($item->isDir()) {
            @mkdir($target, 0775, true);
            continue;
        }

        copy($item->getPathname(), $target);
    }
}

$manifestSrc = $root . '/manifest';
$manifestDst = $root . '/packages/com_pastarter/admin/manifest';
rmTree($manifestDst);
@mkdir($manifestDst, 0775, true);

foreach (glob($manifestSrc . '/*.{json,html}', GLOB_BRACE) ?: [] as $berkas) {
    copy($berkas, $manifestDst . '/' . basename($berkas));
}

// Aset visual contoh ikut dikemas di com_pastarter agar disalin otomatis ke /images/ saat instalasi web.
$assetsDst = $root . '/packages/com_pastarter/admin/assets';
rmTree($assetsDst);
@mkdir($assetsDst, 0775, true);

foreach (['flyer', 'ikon', 'konten', 'logo', 'og', 'slider'] as $sub) {
    copyTree($root . '/assets/' . $sub, $assetsDst . '/' . $sub);
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
        if ($file->isDir()) {
            continue;
        }

        $base = $file->getFilename();
        if ($base === '.DS_Store' || $base === 'Thumbs.db') {
            continue;
        }

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

/**
 * Membangun arsip zip dari daftar pasangan [path_asal => path_dalam_zip].
 *
 * @param array<string, string> $entries
 * @param array<string, string> $strings
 * @return array{0:string,1:int,2:string}
 */
function zipEntries(string $zipPath, array $entries, array $strings = []): array
{
    @unlink($zipPath);
    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
        throw new RuntimeException("Tidak bisa membuat {$zipPath}");
    }

    foreach ($entries as $src => $arcName) {
        if (!is_file($src)) {
            throw new RuntimeException("Berkas tidak ditemukan: {$src}");
        }
        $zip->addFile($src, $arcName);
    }

    foreach ($strings as $arcName => $content) {
        $zip->addFromString($arcName, $content);
    }

    $zip->close();

    return [$zipPath, (int) filesize($zipPath), (string) hash_file('sha256', $zipPath)];
}

$targets = [
    'com_pastarter'   => $root . '/packages/com_pastarter',
    'tpl_pa_satker'   => $root . '/packages/tpl_pa_satker',
    'mod_pa_jamkerja' => $root . '/packages/mod_pa_jamkerja',
    'mod_pa_agenda'   => $root . '/packages/mod_pa_agenda',
    'mod_pa_bahasa'   => $root . '/packages/mod_pa_bahasa',
];

$hasil = [];
foreach ($targets as $nama => $dir) {
    $hasil[$nama] = zipDir($dir, $dist . '/' . $nama . '.zip');
}

// Paket gabungan 1x unggah (pkg_pastarter.zip).
$pkgEntries = [
    $root . '/packages/pkg_pastarter/pkg_pastarter.xml' => 'pkg_pastarter.xml',
];
foreach (array_keys($targets) as $nama) {
    $pkgEntries[$dist . '/' . $nama . '.zip'] = 'packages/' . $nama . '.zip';
}
$hasil['pkg_pastarter'] = zipEntries($dist . '/pkg_pastarter.zip', $pkgEntries);

$needDocs    = in_array('--docs', $argv, true) || in_array('--release', $argv, true);
$needRelease = in_array('--release', $argv, true);

if ($needDocs) {
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
    fputcsv($fh, ['id', 'aspek', 'no', 'judul', 'url', 'kriteria'], escape: '\\');
    foreach ($rows as $row) {
        fputcsv($fh, $row, escape: '\\');
    }
    fclose($fh);

    echo "docs/SINOVA-URL-MAP.csv: " . count($rows) . " baris\n";
}

if ($needRelease) {
    $prefix  = 'pa-starterpack-ekstensi-v' . PA_RELEASE_VERSION;
    $entries = [
        $root . '/docs/PANDUAN-INSTALASI-EKSTENSI.txt' => $prefix . '/PANDUAN-INSTALASI.txt',
        $dist . '/pkg_pastarter.zip'                   => $prefix . '/pkg_pastarter.zip',
        $root . '/docs/SINOVA-URL-MAP.csv'             => $prefix . '/SINOVA-URL-MAP.csv',
        $root . '/LICENSE'                             => $prefix . '/LICENSE.txt',
    ];

    foreach (array_keys($targets) as $nama) {
        $entries[$dist . '/' . $nama . '.zip'] = $prefix . '/ekstensi/' . $nama . '.zip';
    }

    $checksumLines = [];
    foreach ($entries as $src => $arc) {
        $rel = substr($arc, strlen($prefix) + 1);
        $checksumLines[] = hash_file('sha256', $src) . '  ' . $rel;
    }

    $bundleName         = $prefix;
    $hasil[$bundleName] = zipEntries(
        $dist . '/' . $bundleName . '.zip',
        $entries,
        [$prefix . '/CHECKSUMS.sha256' => implode("\n", $checksumLines) . "\n"]
    );
}

printf("%-34s %10s  %s\n", 'paket', 'byte', 'sha256');
foreach ($hasil as $nama => [$path, $size, $hash]) {
    printf("%-34s %10d  %s\n", $nama, $size, $hash);
}
