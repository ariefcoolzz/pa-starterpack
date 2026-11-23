<?php
/**
 * Override halaman artikel — pola situs Badilag: kartu putih satu kolom,
 * judul di tengah, baris identitas (penulis, terbit, dibaca), garis pemisah,
 * gambar utama, lalu isi artikel dengan lebar baca yang nyaman.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\Document\HtmlDocument $this */

$item = $this->item;
$text = HTMLHelper::_('content.prepare', ($item->introtext ?? '') . ($item->fulltext ?? ''), '', 'com_content.article');

$gambar  = json_decode((string) ($item->images ?? '{}'), true) ?: [];
$src     = (string) ($gambar['image_intro'] ?? '');
$alt     = (string) ($gambar['image_intro_alt'] ?? $item->title);
$caption = (string) ($gambar['image_intro_caption'] ?? '');

$penulis = trim((string) ($item->author ?? ''));
$hits    = (int) ($item->hits ?? 0);

// Tautan kembali ke daftar artikel memakai menu kategori bila tersedia,
// supaya pengunjung tidak terlempar ke URL komponen mentah.
$kembali = '';
$catid   = (int) ($item->catid ?? 0);

if ($catid > 0) {
    $menuSitus = Factory::getApplication()->getMenu('site');
    $cocok     = $menuSitus->getItems('link', 'index.php?option=com_content&view=category&id=' . $catid, true);

    if (!empty($cocok)) {
        $menu    = is_array($cocok) ? reset($cocok) : $cocok;
        $kembali = Route::_('index.php?Itemid=' . (int) $menu->id);
    }
}
?>
<article class="pa-article">
    <header class="pa-article__head">
        <?php if (!empty($item->category_title)) : ?>
            <span class="pa-tag"><?php echo htmlspecialchars((string) $item->category_title, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <h1 class="pa-article__title"><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></h1>

        <p class="pa-article__meta">
            <?php if ($penulis !== '') : ?>
                <span>Ditulis: <?php echo htmlspecialchars($penulis, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <span>Terbit: <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>"><?php echo HTMLHelper::_('date', $item->publish_up, 'j F Y'); ?></time></span>
            <?php if ($hits > 0) : ?>
                <span>Dibaca: <?php echo number_format($hits, 0, ',', '.'); ?> kali</span>
            <?php endif; ?>
        </p>
    </header>

    <?php if ($src !== '') : ?>
        <figure class="pa-article__figure">
            <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>"
                 loading="eager" decoding="async">
            <?php if ($caption !== '') : ?>
                <figcaption><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <div class="pa-article__body">
        <?php echo $text; ?>
    </div>

    <?php if ($kembali !== '') : ?>
        <footer class="pa-article__foot">
            <a class="pa-article__back" href="<?php echo $kembali; ?>">
                <span aria-hidden="true">&larr;</span> Kembali ke daftar <?php echo htmlspecialchars((string) ($item->category_title ?: 'artikel'), ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </footer>
    <?php endif; ?>
</article>
