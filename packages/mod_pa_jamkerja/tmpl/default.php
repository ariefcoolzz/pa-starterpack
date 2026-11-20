<?php
/**
 * mod_pa_jamkerja — jam layanan PTSP + status buka/tutup.
 *
 * Status dihitung di sisi klien memakai jam perangkat (WIB diatur lewat parameter
 * zona waktu), dengan tampilan default "buka" bila JavaScript mati. Tidak ada
 * permintaan jaringan, jadi aman di jaringan lambat.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$module  = $module ?? null;
$params  = $params ?? null;

$zona = (string) $params->get('zona', 'Asia/Jakarta');
$hari = [
    'senin' => (string) $params->get('senin', '08.00–16.30'),
    'jumat' => (string) $params->get('jumat', '07.30–16.00'),
];
$istirahat = (string) $params->get('istirahat', '12.00–13.00');
$catatan   = (string) $params->get('catatan', 'Sabtu, Minggu, dan hari libur nasional: tutup.');
$prioritas = (string) $params->get('prioritas', 'Layanan prioritas disabilitas, lansia, dan ibu hamil tersedia setiap hari kerja.');

$jamBuka = (int) $params->get('jam_buka', 8);
$jamTutup = (int) $params->get('jam_tutup', 16);
?>
<section class="pa-jam" data-pa-jam data-zona="<?php echo htmlspecialchars($zona, ENT_QUOTES, 'UTF-8'); ?>"
         data-buka="<?php echo $jamBuka; ?>" data-tutup="<?php echo $jamTutup; ?>">
    <h2>
        <span>Jam layanan PTSP</span>
        <span class="pa-jam__status">
            <span class="pa-jam__dot" data-pa-jam-dot></span>
            <span data-pa-jam-label>Buka</span>
        </span>
    </h2>
    <dl>
        <dt>Senin–Kamis</dt><dd><?php echo htmlspecialchars($hari['senin'], ENT_QUOTES, 'UTF-8'); ?></dd>
        <dt>Jumat</dt><dd><?php echo htmlspecialchars($hari['jumat'], ENT_QUOTES, 'UTF-8'); ?></dd>
        <dt>Istirahat</dt><dd><?php echo htmlspecialchars($istirahat, ENT_QUOTES, 'UTF-8'); ?></dd>
    </dl>
    <?php if ($catatan !== '') : ?>
        <p class="pa-jam__note"><?php echo htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php if ($prioritas !== '') : ?>
        <p class="pa-jam__prioritas"><?php echo htmlspecialchars($prioritas, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
</section>
<script>
(function () {
    var el = document.currentScript.previousElementSibling;
    if (!el || !el.hasAttribute('data-pa-jam')) { return; }
    var label = el.querySelector('[data-pa-jam-label]');
    var dot = el.querySelector('[data-pa-jam-dot]');
    var buka = parseInt(el.dataset.buka, 10) || 8;
    var tutup = parseInt(el.dataset.tutup, 10) || 16;

    function perbarui() {
        var now = new Date();
        var hari = now.getDay(); // 0 Minggu, 6 Sabtu
        var jam = now.getHours();
        var kerja = hari >= 1 && hari <= 5;
        var dalamJam = jam >= buka && jam < tutup;
        var aktif = kerja && dalamJam;
        label.textContent = aktif ? 'Buka' : 'Tutup';
        dot.className = 'pa-jam__dot' + (aktif ? '' : ' pa-jam__dot--off');
        el.setAttribute('data-status', aktif ? 'buka' : 'tutup');
    }

    perbarui();
    window.setInterval(perbarui, 60000);
})();
</script>
