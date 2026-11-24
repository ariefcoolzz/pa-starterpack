<?php
declare(strict_types=1);

/**
 * Membangun paket rilis Quickstart Joomla 6.2.0 + PA Starterpack siap unduh.
 *
 *   php tools/build-quickstart.php
 *   php tools/build-quickstart.php --joomla-zip=/path/Joomla_6.2.0-Stable-Full_Package.zip --sandbox=/path/pa-satker-sandbox
 */

const PA_QS_VERSION  = '1.0.0';
const JOOMLA_62_URL  = 'https://github.com/joomla/joomla-cms/releases/download/6.2.0/Joomla_6.2.0-Stable-Full_Package.zip';

$root = dirname(__DIR__);
$dist = $root . '/dist';
@mkdir($dist, 0775, true);

$opts      = getopt('', ['joomla-zip::', 'sandbox::', 'keep-staging']);
$joomlaZip = is_string($opts['joomla-zip'] ?? null) && $opts['joomla-zip'] !== ''
    ? $opts['joomla-zip']
    : '/tmp/Joomla_6.2.0-Stable-Full_Package.zip';
$sandbox   = is_string($opts['sandbox'] ?? null) && $opts['sandbox'] !== ''
    ? rtrim($opts['sandbox'], '/\\')
    : dirname($root) . '/pa-satker-sandbox';
$keepStage = array_key_exists('keep-staging', $opts);

function rmDirRecursive(string $dir): void
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

function copyDirClean(string $src, string $dst): void
{
    if (!is_dir($src)) {
        throw new RuntimeException("Direktori sumber tidak ada: {$src}");
    }

    @mkdir($dst, 0775, true);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it as $item) {
        $name = $item->getFilename();
        if ($name === '.DS_Store' || $name === 'Thumbs.db') {
            continue;
        }

        $rel    = substr($item->getPathname(), strlen($src) + 1);
        $target = $dst . '/' . $rel;

        if ($item->isDir()) {
            @mkdir($target, 0775, true);
            continue;
        }

        @mkdir(dirname($target), 0775, true);
        copy($item->getPathname(), $target);
    }
}

function readSandboxDbConfig(string $sandboxDir): array
{
    $cfgFile = $sandboxDir . '/configuration.php';
    if (!is_file($cfgFile)) {
        throw new RuntimeException("configuration.php sandbox tidak ditemukan: {$cfgFile}");
    }

    require_once $cfgFile;
    if (!class_exists('JConfig')) {
        throw new RuntimeException('Kelas JConfig tidak ditemukan di configuration.php sandbox.');
    }

    $cfg  = new \JConfig();
    $host = (string) $cfg->host;
    $port = 3306;
    if (str_contains($host, ':')) {
        [$host, $portStr] = explode(':', $host, 2);
        $port = (int) $portStr;
    }

    return [
        'host'   => $host,
        'port'   => $port,
        'user'   => (string) $cfg->user,
        'pass'   => (string) $cfg->password,
        'db'     => (string) $cfg->db,
        'prefix' => (string) $cfg->dbprefix,
    ];
}

function sqlVal(mysqli $db, mixed $val): string
{
    if ($val === null) {
        return 'NULL';
    }

    return "'" . $db->real_escape_string((string) $val) . "'";
}

/**
 * Mengekspor satu tabel ke kumpulan INSERT satu-baris memakai prefix #__.
 *
 * @param array<string, mixed> $overrides
 */
function exportTableInserts(
    mysqli $db,
    string $prefix,
    string $logicalTable,
    string $where = '1=1',
    string $orderBy = '',
    array $overrides = [],
    array $skipColumns = []
): array {
    $physical = $prefix . $logicalTable;
    $sql      = "SELECT * FROM `{$physical}` WHERE {$where}" . ($orderBy !== '' ? " ORDER BY {$orderBy}" : '');
    $res      = $db->query($sql);

    if (!$res) {
        throw new RuntimeException("Query gagal pada {$physical}: " . $db->error);
    }

    $lines = [];
    while ($row = $res->fetch_assoc()) {
        foreach ($skipColumns as $col) {
            unset($row[$col]);
        }
        foreach ($overrides as $col => $val) {
            if (array_key_exists($col, $row)) {
                $row[$col] = $val;
            }
        }

        $cols = array_map(static fn (string $c): string => '`' . $c . '`', array_keys($row));
        $vals = array_map(static fn (mixed $v): string => sqlVal($db, $v), array_values($row));

        $line = 'INSERT INTO `#__' . $logicalTable . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ');';
        if (str_contains($line, "\n") || str_contains($line, "\r")) {
            throw new RuntimeException("Baris SQL mengandung newline mentah pada {$logicalTable}");
        }
        $lines[] = $line;
    }

    $res->free();

    return $lines;
}

function buildCustomSql(array $dbCfg): string
{
    $mysqli = new mysqli($dbCfg['host'], $dbCfg['user'], $dbCfg['pass'], $dbCfg['db'], $dbCfg['port']);
    if ($mysqli->connect_errno) {
        throw new RuntimeException('Koneksi DB sandbox gagal: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    $p = $dbCfg['prefix'];

    $sql   = [];
    $sql[] = '-- PA Starterpack Quickstart SQL (Joomla 6.2.0)';
    $sql[] = 'SET NAMES utf8mb4;';

    // 1. Ekstensi PA Starter (extension_id auto_increment, package_id disambung setelah insert).
    $extList = "'pa_satker','com_pastarter','mod_pa_agenda','mod_pa_jamkerja','mod_pa_bahasa','pkg_pastarter'";
    $sql     = array_merge($sql, exportTableInserts(
        $mysqli,
        $p,
        'extensions',
        "`element` IN ({$extList})",
        '`extension_id` ASC',
        ['package_id' => 0, 'checked_out' => null, 'checked_out_time' => null],
        ['extension_id']
    ));

    $sql[] = "UPDATE `#__extensions` SET `package_id` = (SELECT `eid` FROM (SELECT `extension_id` AS `eid` FROM `#__extensions` WHERE `element` = 'pkg_pastarter' AND `type` = 'package' LIMIT 1) AS `tmp`) WHERE `element` IN ('pa_satker', 'com_pastarter', 'mod_pa_agenda', 'mod_pa_jamkerja', 'mod_pa_bahasa');";
    $sql[] = "UPDATE `#__extensions` SET `enabled` = 0 WHERE `type` = 'plugin' AND `folder` = 'content' AND `element` = 'emailcloak' AND `client_id` = 0;";
    $sql[] = "UPDATE `#__extensions` SET `enabled` = 1 WHERE `type` = 'plugin' AND `client_id` = 0 AND ((`folder` = 'system' AND `element` IN ('accessibility','skipto','httpheaders','sef')) OR (`folder` = 'content' AND `element` = 'loadmodule'));";

    // 2. Template style pa_satker sebagai default frontend (client_id = 0), biarkan atum (client_id = 1).
    $sql[] = "UPDATE `#__template_styles` SET `home` = '0' WHERE `client_id` = 0;";
    $sql   = array_merge($sql, exportTableInserts(
        $mysqli,
        $p,
        'template_styles',
        "`template` = 'pa_satker' AND `client_id` = 0",
        '`id` ASC',
        ['title' => 'pa_satker - Default', 'home' => '1'],
        ['id']
    ));

    // 3. Bahasa konten Indonesia (INSERT IGNORE bila sudah ada).
    foreach (exportTableInserts($mysqli, $p, 'languages', "`lang_code` = 'id-ID'", '`lang_id` ASC', [], ['lang_id']) as $line) {
        $sql[] = preg_replace('/^INSERT INTO /', 'INSERT IGNORE INTO ', $line, 1);
    }

    // 4. Data konten, kategori, menu, modul, workflow associations, dan assets.
    $cleanTables = [
        'modules_menu'          => ['1=1', '`moduleid` ASC, `menuid` ASC', []],
        'modules'               => ['1=1', '`id` ASC', ['checked_out' => null, 'checked_out_time' => null]],
        'menu'                  => ['1=1', '`lft` ASC', ['checked_out' => null, 'checked_out_time' => null]],
        'menu_types'            => ['1=1', '`id` ASC', []],
        'workflow_associations' => ['1=1', '`item_id` ASC', []],
        'content'               => ['1=1', '`id` ASC', [
            'created_by'       => 0,
            'modified_by'      => 0,
            'created_by_alias' => 'Redaksi Satker',
            'hits'             => 0,
            'checked_out'      => null,
            'checked_out_time' => null,
        ]],
        'categories'            => ['1=1', '`lft` ASC', [
            'created_user_id'  => 0,
            'modified_user_id' => 0,
            'checked_out'      => null,
            'checked_out_time' => null,
        ]],
        'assets'                => ['1=1', '`lft` ASC', []],
    ];

    foreach (array_keys($cleanTables) as $tbl) {
        $sql[] = "DELETE FROM `#__{$tbl}`;";
    }

    foreach (array_reverse($cleanTables, true) as $tbl => [$where, $order, $overrides]) {
        $sql = array_merge($sql, exportTableInserts($mysqli, $p, $tbl, $where, $order, $overrides));
    }

    // 5. Resolusi dinamis component_id menu setelah insert #__extensions & #__menu.
    $sql[] = "UPDATE `#__menu` SET `component_id` = (SELECT `extension_id` FROM `#__extensions` WHERE `element` = 'com_pastarter' AND `type` = 'component' LIMIT 1) WHERE `client_id` = 1 AND `link` LIKE 'index.php?option=com_pastarter%';";
    $sql[] = "UPDATE `#__menu` SET `component_id` = (SELECT `extension_id` FROM `#__extensions` WHERE `element` = 'com_content' AND `type` = 'component' LIMIT 1) WHERE `link` LIKE 'index.php?option=com_content%';";

    $mysqli->close();

    return implode("\n", $sql) . "\n";
}

// 1. Pastikan paket zip inti Joomla 6.2.0 tersedia.
if (!is_file($joomlaZip)) {
    echo "Mengunduh Joomla 6.2.0 ke {$joomlaZip}...\n";
    @mkdir(dirname($joomlaZip), 0775, true);
    $cmd = 'curl -fL --max-time 180 -o ' . escapeshellarg($joomlaZip) . ' ' . escapeshellarg(JOOMLA_62_URL);
    passthru($cmd, $code);
    if ($code !== 0 || !is_file($joomlaZip)) {
        throw new RuntimeException('Gagal mengunduh Joomla 6.2.0.');
    }
}

// 2. Pastikan aset & manifest di packages/com_pastarter sudah tersinkron.
$manifestDst = $root . '/packages/com_pastarter/admin/manifest';
rmDirRecursive($manifestDst);
@mkdir($manifestDst, 0775, true);
foreach (glob($root . '/manifest/*.{json,html}', GLOB_BRACE) ?: [] as $berkas) {
    copy($berkas, $manifestDst . '/' . basename($berkas));
}
$assetsDst = $root . '/packages/com_pastarter/admin/assets';
rmDirRecursive($assetsDst);
foreach (['flyer', 'ikon', 'konten', 'logo', 'og', 'slider'] as $sub) {
    copyDirClean($root . '/assets/' . $sub, $assetsDst . '/' . $sub);
}

// 3. Siapkan staging /tmp/pa-qs-build/.
$stageRoot  = '/tmp/pa-qs-build';
$bundleRoot = 'pa-starterpack-quickstart-joomla62-v' . PA_QS_VERSION;
$joomlaDir  = $stageRoot . '/' . $bundleRoot . '/joomla';
rmDirRecursive($stageRoot);
@mkdir($joomlaDir, 0775, true);

$zip = new ZipArchive();
if ($zip->open($joomlaZip) !== true) {
    throw new RuntimeException("Tidak bisa membuka {$joomlaZip}");
}
$zip->extractTo($joomlaDir);
$zip->close();

// 4. Salin ekstensi PA Starter, aset, bahasa, manifest package, dan CLI ke staging joomla/.
copyDirClean($root . '/packages/tpl_pa_satker', $joomlaDir . '/templates/pa_satker');
copyDirClean($root . '/packages/com_pastarter/admin', $joomlaDir . '/administrator/components/com_pastarter');
copy($root . '/packages/com_pastarter/pastarter.xml', $joomlaDir . '/administrator/components/com_pastarter/pastarter.xml');
copy($root . '/packages/com_pastarter/script.php', $joomlaDir . '/administrator/components/com_pastarter/script.php');
copyDirClean($root . '/packages/com_pastarter/admin/language/en-GB', $joomlaDir . '/administrator/language/en-GB');

foreach (['mod_pa_agenda', 'mod_pa_jamkerja', 'mod_pa_bahasa'] as $mod) {
    copyDirClean($root . '/packages/' . $mod, $joomlaDir . '/modules/' . $mod);
    copyDirClean($root . '/packages/' . $mod . '/language/en-GB', $joomlaDir . '/language/en-GB');
}

@mkdir($joomlaDir . '/administrator/manifests/packages', 0775, true);
copy($root . '/packages/pkg_pastarter/pkg_pastarter.xml', $joomlaDir . '/administrator/manifests/packages/pkg_pastarter.xml');

foreach (['flyer', 'ikon', 'konten', 'logo', 'og', 'slider'] as $sub) {
    copyDirClean($root . '/assets/' . $sub, $joomlaDir . '/images/' . $sub);
}

copy($root . '/tools/pa.php', $joomlaDir . '/cli/pa.php');
chmod($joomlaDir . '/cli/pa.php', 0755);

// Pastikan staging steril dari file konfigurasi/cache lokal.
@unlink($joomlaDir . '/configuration.php');
@unlink($joomlaDir . '/administrator/cache/autoload_psr4.php');

// 5. Bangkitkan installation/sql/mysql/custom.sql dari DB sandbox terverifikasi.
$dbCfg     = readSandboxDbConfig($sandbox);
$customSql = buildCustomSql($dbCfg);
$sqlTarget = $joomlaDir . '/installation/sql/mysql/custom.sql';
file_put_contents($sqlTarget, $customSql);

// 6. Tambahkan file pendukung bundle di root arsip.
$bundleDir = $stageRoot . '/' . $bundleRoot;
copy($root . '/docs/PANDUAN-INSTALASI-QUICKSTART.txt', $bundleDir . '/PANDUAN-INSTALASI.txt');
copy($root . '/LICENSE', $bundleDir . '/LICENSE.txt');

if (!is_file($root . '/docs/SINOVA-URL-MAP.csv')) {
    throw new RuntimeException('docs/SINOVA-URL-MAP.csv belum ada. Jalankan php tools/build.php --docs dulu.');
}
copy($root . '/docs/SINOVA-URL-MAP.csv', $bundleDir . '/SINOVA-URL-MAP.csv');

$checksumContent = implode("\n", [
    hash_file('sha256', $bundleDir . '/PANDUAN-INSTALASI.txt') . '  PANDUAN-INSTALASI.txt',
    hash_file('sha256', $bundleDir . '/SINOVA-URL-MAP.csv') . '  SINOVA-URL-MAP.csv',
    hash_file('sha256', $bundleDir . '/LICENSE.txt') . '  LICENSE.txt',
    hash_file('sha256', $sqlTarget) . '  joomla/installation/sql/mysql/custom.sql',
]) . "\n";
file_put_contents($bundleDir . '/CHECKSUMS.sha256', $checksumContent);

// 7. Kemas menjadi zip rilis di dist/.
$outZip = $dist . '/' . $bundleRoot . '.zip';
@unlink($outZip);
$out = new ZipArchive();
if ($out->open($outZip, ZipArchive::CREATE) !== true) {
    throw new RuntimeException("Tidak bisa membuat {$outZip}");
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($bundleDir, FilesystemIterator::SKIP_DOTS)
);

$count = 0;
foreach ($it as $file) {
    if ($file->isDir()) {
        continue;
    }
    $name = $file->getFilename();
    if ($name === '.DS_Store' || $name === 'Thumbs.db') {
        continue;
    }
    $rel = $bundleRoot . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($bundleDir) + 1));
    $out->addFile($file->getPathname(), $rel);
    $count++;
}
$out->close();

if (!$keepStage) {
    rmDirRecursive($stageRoot);
}

printf(
    "Quickstart siap: %s\nBerkas: %d | Ukuran: %d byte | SHA-256: %s\n",
    $outZip,
    $count,
    (int) filesize($outZip),
    (string) hash_file('sha256', $outZip)
);
