<?php
/**
 * Daftar artikel: mode "featured" (satu headline besar + kartu) atau grid biasa.
 * Dipakai di beranda (berita & pengumuman) dan bisa dipakai halaman lain.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$list      = $list ?? [];
$modParams = $params ?? null;
$class     = htmlspecialchars((string) ($modParams?->get('moduleclass_sfx', '') ?? ''), ENT_QUOTES, 'UTF-8');
$layout    = (string) ($modParams?->get('pa_layout', 'grid') ?? 'grid');
$kelas     = $layout === 'featured' ? 'pa-news--featured' : '';
$showCat   = (int) ($modParams?->get('show_category', 1) ?? 1);
$showIntro = (int) ($modParams?->get('show_introtext', 1) ?? 1);
$introLen  = (int) ($modParams?->get('introtext_limit', 140) ?? 140);

/*
 * Tautan artikel memakai Itemid item menu daftar kategori. Tanpa Itemid,
 * Joomla tidak menemukan basis SEF kategori dan menghasilkan
 * /component/content/article/<alias>?catid=.. — tampak rusak bagi pengunjung.
 */
$menuSitus     = Factory::getApplication()->getMenu('site');
$tautanArtikel = static function ($item) use ($menuSitus): string {
    $catid = (int) ($item->catid ?? 0);
    $alias = (string) ($item->alias ?? '');

    if ($catid > 0 && $alias !== '') {
        $cocok = $menuSitus->getItems('link', 'index.php?option=com_content&view=category&id=' . $catid, true);

        if (!empty($cocok)) {
            $menu   = is_array($cocok) ? reset($cocok) : $cocok;
            $prefix = trim((string) ($menu->route ?? $menu->path ?? ''), '/');

            if ($prefix !== '') {
                // Jalur menu daftar kategori + alias artikel = URL yang sudah
                // terbukti merespons 200 (lihat verifikasi di README).
                return \Joomla\CMS\Uri\Uri::root(true) . '/' . $prefix . '/' . $alias;
            }
        }
    }

    $slug = (int) $item->id . ':' . $alias;

    return Route::_(
        'index.php?option=com_content&view=article&id=' . $slug
        . ($catid > 0 ? '&catid=' . $catid : '')
    );
};
?>
<?php if ($list === []) : ?>
    <p class="pa-muted">Belum ada artikel pada kategori ini.</p>
<?php else : ?>
    <?php if ($layout === 'cards') : ?>
        <ul class="pa-announ pa-announ--cards">
            <?php foreach ($list as $item) : ?>
                <li>
                    <a class="pa-announ__card" href="<?php echo $tautanArtikel($item); ?>">
                        <time class="pa-announ__date" datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                            <span class="pa-announ__day"><?php echo HTMLHelper::_('date', $item->publish_up, 'd'); ?></span>
                            <span class="pa-announ__month"><?php echo HTMLHelper::_('date', $item->publish_up, 'M Y'); ?></span>
                        </time>
                        <span class="pa-announ__title"><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="pa-announ__arrow" aria-hidden="true">&rsaquo;</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($layout === 'bar' || $layout === 'strip') : ?>
        <ul class="pa-announ<?php echo $layout === 'strip' ? ' pa-announ--strip' : ''; ?>">
            <?php foreach ($list as $item) : ?>
                <li>
                    <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                        <?php echo HTMLHelper::_('date', $item->publish_up, 'd M Y'); ?>
                    </time>
                    <a href="<?php echo $tautanArtikel($item); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
    <ul class="pa-news <?php echo $kelas; ?> <?php echo $class; ?>">
        <?php foreach ($list as $posisi => $item) : ?>
            <?php
            $images = json_decode((string) ($item->images ?? '{}'), true) ?: [];
            $src    = (string) ($images['image_intro'] ?? '');
            $alt    = (string) ($images['image_intro_alt'] ?? $item->title);

            /*
             * Ringkasan diambil dari introtext; bila kosong (artikel yang seluruh
             * isinya ditaruh di kolom "isi penuh"), dipotong dari fulltext supaya
             * kartu berita tidak menggantung kosong di bawah judul. Kartu utama
             * diberi jatah lebih panjang karena ruangnya lebih lega.
             */
            $sumber  = trim((string) ($item->introtext ?? '')) !== ''
                ? (string) $item->introtext
                : (string) ($item->fulltext ?? '');
            $ringkas = trim((string) preg_replace('/\s+/u', ' ', strip_tags($sumber)));
            $batas   = $posisi === 0 ? max($introLen, 215) : $introLen;

            if ($ringkas !== '') {
                $ringkas = HTMLHelper::_('string.truncate', $ringkas, $batas, true, false);
            }
            ?>
            <li>
                <article>
                    <?php if ($src !== '') : ?>
                        <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'); ?>"
                             loading="lazy" decoding="async">
                    <?php endif; ?>
                    <div class="pa-news__body">
                        <?php if ($showCat === 1 && !empty($item->category_title)) : ?>
                            <span class="pa-tag"><?php echo htmlspecialchars((string) $item->category_title, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <h3><a href="<?php echo $tautanArtikel($item); ?>">
                            <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a></h3>
                        <?php if ($showIntro === 1 && $ringkas !== '') : ?>
                            <p class="pa-news__excerpt"><?php echo htmlspecialchars($ringkas, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                            <?php echo HTMLHelper::_('date', $item->publish_up, 'd M Y'); ?>
                        </time>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
<?php endif; ?>
