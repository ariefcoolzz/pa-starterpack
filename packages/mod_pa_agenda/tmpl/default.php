<?php
/**
 * Agenda sidang: tabel ringkas untuk beranda.
 * Data berasal dari parameter modul (subform), bukan dari SIPP — operator
 * menyalin baris hari ini. Tautan "jadwal lengkap" menuju halaman SIPP satker.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$items = $params->get('items', []);
$items = is_array($items) ? $items : (array) $items;

$catatan = trim((string) $params->get('catatan', ''));
$tautan  = trim((string) $params->get('tautan', ''));

$label = [
    'sidang'  => 'Sidang',
    'putusan' => 'Putusan',
    'mediasi' => 'Mediasi',
    'ditunda' => 'Ditunda',
];
?>
<section class="pa-agenda">
    <div class="pa-agenda__head">
        <h2>Agenda sidang hari ini</h2>
        <?php if ($tautan !== '') : ?>
            <a class="pa-more" href="<?php echo htmlspecialchars($tautan, ENT_QUOTES, 'UTF-8'); ?>">Jadwal lengkap</a>
        <?php endif; ?>
    </div>

    <?php if ($items === []) : ?>
        <p class="pa-muted">Belum ada agenda yang diisi. Lengkapi melalui pengaturan modul.</p>
    <?php else : ?>
        <div class="pa-agenda__scroll">
            <table class="pa-agenda__table">
                <caption class="visually-hidden">Agenda sidang hari ini</caption>
                <thead>
                    <tr>
                        <th scope="col">Jam</th>
                        <th scope="col">Perkara</th>
                        <th scope="col">Ruang</th>
                        <th scope="col">Majelis</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $baris) : ?>
                        <?php
                        $baris  = (array) $baris;
                        $status = (string) ($baris['status'] ?? 'sidang');
                        ?>
                        <tr>
                            <th scope="row" data-label="Jam"><?php echo htmlspecialchars((string) ($baris['jam'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></th>
                            <td data-label="Perkara"><?php echo htmlspecialchars((string) ($baris['perkara'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Ruang"><?php echo htmlspecialchars((string) ($baris['ruang'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Majelis"><?php echo htmlspecialchars((string) ($baris['majelis'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Status"><span class="pa-status pa-status--<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($label[$status] ?? $status, ENT_QUOTES, 'UTF-8'); ?>
                            </span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($catatan !== '') : ?>
        <p class="pa-agenda__note"><?php echo htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
</section>
