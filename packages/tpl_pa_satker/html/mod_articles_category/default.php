<?php
/**
 * Daftar artikel: mode "featured" (satu headline besar + kartu) atau grid biasa.
 * Dipakai di beranda (berita & pengumuman) dan bisa dipakai halaman lain.
 */
defined('_JEXEC') or die;

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
?>
<?php if ($list === []) : ?>
    <p class="pa-muted">Belum ada artikel pada kategori ini.</p>
<?php else : ?>
    <?php if ($layout === 'bar') : ?>
        <ul class="pa-announ">
            <?php foreach ($list as $item) : ?>
                <li>
                    <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                        <?php echo HTMLHelper::_('date', $item->publish_up, 'd M'); ?>
                    </time>
                    <a href="<?php echo Route::_(RouteHelper::getArticleRoute($item->id, $item->catid, $item->language ?? '*')); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
    <ul class="pa-news <?php echo $kelas; ?> <?php echo $class; ?>">
        <?php foreach ($list as $item) : ?>
            <?php
            $images = json_decode((string) ($item->images ?? '{}'), true) ?: [];
            $src    = (string) ($images['image_intro'] ?? '');
            $alt    = (string) ($images['image_intro_alt'] ?? $item->title);
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
                        <h3><a href="<?php echo Route::_(RouteHelper::getArticleRoute($item->id, $item->catid, $item->language ?? '*')); ?>">
                            <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a></h3>
                        <?php if ($showIntro === 1) : ?>
                            <p><?php echo HTMLHelper::_('string.truncate', strip_tags((string) ($item->introtext ?? '')), $introLen); ?></p>
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
