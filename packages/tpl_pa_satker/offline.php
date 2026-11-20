<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */
$app  = Factory::getApplication();
$root = Uri::root(true);
$wa   = $this->getWebAssetManager();
$wa->useStyle('pa.template');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8'); ?> — Pemeliharaan</title>
    <jdoc:include type="styles" />
</head>
<body class="pa">
<main id="pa-main" class="pa-main"><div class="pa-wrap"><div class="pa-content">
    <h1>Situs sedang dalam pemeliharaan</h1>
    <p>Kami sedang memperbarui layanan. Silakan kembali beberapa saat lagi.</p>
    <?php if ($this->params->get('display_offline_message', 1)) : ?>
        <p><?php echo $this->params->get('offline_message'); ?></p>
    <?php endif; ?>
</div></div></main>
</body>
</html>
