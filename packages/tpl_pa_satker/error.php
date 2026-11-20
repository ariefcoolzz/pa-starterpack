<?php
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\ErrorDocument $this */
$root  = Uri::root(true);
$kode  = (int) $this->error->getCode();
$pesan = htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8');
$judul = $kode === 404 ? 'Halaman tidak ditemukan' : 'Terjadi kesalahan';
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $judul; ?> — <?php echo htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo $root; ?>/templates/pa_satker/css/template.css">
</head>
<body class="pa">
<main id="pa-main" class="pa-main"><div class="pa-wrap"><div class="pa-content">
    <h1><?php echo $judul; ?> (<?php echo $kode; ?>)</h1>
    <p><?php echo $pesan; ?></p>
    <p>Silakan kembali ke <a href="<?php echo $root; ?>/">beranda</a> atau gunakan pencarian pada menu utama.</p>
</div></div></main>
</body>
</html>
