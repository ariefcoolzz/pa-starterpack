<?php
declare(strict_types=1);

defined('_JEXEC') or die;

/**
 * Skrip instalasi com_pastarter: menyalin aset visual bawaan ke /images/
 * hanya jika berkas tujuan belum ada (tidak menimpa logo/foto milik satker).
 */
final class com_pastarterInstallerScript
{
    private const SUBFOLDERS = ['flyer', 'ikon', 'konten', 'logo', 'og', 'slider'];

    public function postflight(string $type, mixed $parent): bool
    {
        if (!in_array($type, ['install', 'update', 'discover_install'], true)) {
            return true;
        }

        $srcRoot = JPATH_ADMINISTRATOR . '/components/com_pastarter/assets';
        $dstRoot = JPATH_ROOT . '/images';

        if (!is_dir($srcRoot)) {
            return true;
        }

        foreach (self::SUBFOLDERS as $sub) {
            $srcDir = $srcRoot . '/' . $sub;
            if (!is_dir($srcDir)) {
                continue;
            }

            $this->copyMissingFiles($srcDir, $dstRoot . '/' . $sub);
        }

        return true;
    }

    private function copyMissingFiles(string $srcDir, string $dstDir): void
    {
        if (!is_dir($dstDir) && !@mkdir($dstDir, 0755, true) && !is_dir($dstDir)) {
            return;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $name = $item->getFilename();
            if ($name === '' || $name[0] === '.') {
                continue;
            }

            $rel    = substr($item->getPathname(), strlen($srcDir) + 1);
            $target = $dstDir . '/' . $rel;

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    @mkdir($target, 0755, true);
                }
                continue;
            }

            if (!is_file($target)) {
                @copy($item->getPathname(), $target);
            }
        }
    }
}
