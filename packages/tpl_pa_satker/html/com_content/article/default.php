<?php
/**
 * Override tampilan artikel: satu h1, meta ringkas, konten penuh.
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\CMS\Document\HtmlDocument $this */

$item = $this->item;
$text = HTMLHelper::_('content.prepare', ($item->introtext ?? '') . ($item->fulltext ?? ''), '', 'com_content.article');
?>
<article class="pa-article">
    <h1><?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></h1>

    <p class="pa-meta">
        <time datetime="<?php echo HTMLHelper::_('date', $item->publish_up, 'c'); ?>">
            <?php echo HTMLHelper::_('date', $item->publish_up, Text::_('DATE_FORMAT_LC4')); ?>
        </time>
        <?php if (!empty($item->category_title)) : ?>
            <span aria-hidden="true">·</span>
            <span><?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
    </p>

    <div class="pa-article__body">
        <?php echo $text; ?>
    </div>
</article>
