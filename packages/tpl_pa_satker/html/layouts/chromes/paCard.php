<?php
defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];

if (trim((string) $module->content) === '') {
    return;
}
?>
<section class="pa-panel <?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ($module->showtitle) : ?>
        <div class="pa-panel__head"><h2><?php echo $module->title; ?></h2></div>
    <?php endif; ?>
    <div class="pa-panel__body"><?php echo $module->content; ?></div>
</section>
