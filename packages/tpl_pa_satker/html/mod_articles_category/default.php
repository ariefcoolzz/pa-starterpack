<?php
/**
 * Daftar artikel dalam bentuk kartu (berita beranda, pengumuman, dsb).
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$list = $list ?? [];
$modParams = $params ?? null;
$class = htmlspecialchars((string) ($modParams?->get('moduleclass_sfx', '') ?? ''), ENT_QUOTES, 'UTF-8');
?>
<?php if ($list === []) : ?>
    <p class="pa-muted">Belum ada artikel pada kategori ini.</p>
<?php else : ?>
    <ul class="pa-news <?php echo $class; ?>">
        <?php foreach ($list as $item) : ?>
            <?php
            $images = json_decode((string) ($item->images ?? '{}'), true) ?: [];
            $src    = $images['image_intro'] ?? '';
            ?>
            <li>
                <article>
                    <?php if ($src !== '') : ?>
                        <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <?php if (!empty($item->displayCategoryTitle) || !empty($item->category_title)) : ?>
                        <span class="pa-tag"><?php echo htmlspecialchars((string) ($item->category_title ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <h3><a href="<?php echo Route::_(RouteHelper::getArticleRoute($item->id, $item->catid, $item->language ?? '*')); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a></h3>
                    <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                        <?php echo HTMLHelper::_('date', $item->publish_up, 'd F Y'); ?>
                    </time>
                    <p><?php echo HTMLHelper::_('string.truncate', strip_tags((string) ($item->introtext ?? '')), 130); ?></p>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
