<?php
/**
 * Beranda: kartu artikel berfeatured (dipakai bila menu Home bertipe Featured).
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$items = $this->items ?? [];
?>
<?php if ($items !== []) : ?>
    <ul class="pa-news">
        <?php foreach ($items as $item) : ?>
            <?php
            $images = json_decode((string) ($item->images ?? '{}'), true) ?: [];
            $src    = $images['image_intro'] ?? '';
            ?>
            <li>
                <article>
                    <?php if ($src !== '') : ?>
                        <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" decoding="async">
                    <?php endif; ?>
                    <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
                        <?php echo HTMLHelper::_('date', $item->publish_up, 'd F Y'); ?>
                    </time>
                    <h3><a href="<?php echo Route::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid)); ?>">
                        <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a></h3>
                    <p><?php echo HTMLHelper::_('string.truncate', strip_tags((string) $item->introtext), 140); ?></p>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
