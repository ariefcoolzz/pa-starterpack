<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
?>
<div class="card">
    <div class="card-body">
        <h2>Pasang konten awal</h2>
        <p>Tombol ini membuat kategori, 44 artikel contoh, menu (URL tetap per butir penilaian), dan modul pada template <code>pa_satker</code>.</p>
        <ul>
            <li>Aman dijalankan berulang: konten yang sudah ada tidak ditimpa.</li>
            <li>Jalankan setelah template <code>pa_satker</code> terpasang dan dijadikan default.</li>
            <li>Setelah selesai, buka <strong>Kesiapan SINOVA</strong> untuk melihat halaman yang belum terisi.</li>
        </ul>

        <form action="<?php echo Route::_('index.php?option=com_pastarter'); ?>" method="post">
            <?php echo HTMLHelper::_('form.token'); ?>
            <input type="hidden" name="task" value="seed.run">
            <button type="submit" class="btn btn-primary">Pasang konten awal</button>
        </form>
    </div>
</div>
