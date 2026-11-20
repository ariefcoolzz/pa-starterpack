<?php
defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];

if (trim((string) $module->content) === '') {
    return;
}
?>
<div class="pa-mod pa-mod--plain <?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo $module->content; ?>
</div>
