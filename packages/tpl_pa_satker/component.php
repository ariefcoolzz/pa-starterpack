<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var Joomla\CMS\Document\HtmlDocument $this */
$wa = $this->getWebAssetManager();
$wa->useStyle('pa.template');
$app = Factory::getApplication();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
</head>
<body class="pa pa--component">
    <main class="pa-main"><div class="pa-wrap"><div class="pa-content">
        <jdoc:include type="message" />
        <jdoc:include type="component" />
    </div></div></main>
</body>
</html>
