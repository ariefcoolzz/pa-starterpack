<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

/** @var array<string, mixed> $report */
$report = $this->report;
$est    = $report['estimate'] ?? [];
?>
<div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <strong>Perkiraan mandiri, bukan nilai resmi.</strong> Nilai resmi ditetapkan Tim Penilai melalui aplikasi SINOVA.
    </div>
    <a href="<?php echo Route::_('index.php?option=com_pastarter&view=seed'); ?>" class="btn btn-sm btn-outline-primary">Kembali ke Pasang Konten Awal</a>
</div>

<div class="row mb-3">
    <div class="col-md-3"><div class="card text-center"><div class="card-body">
        <div class="fs-3 fw-bold"><?php echo number_format((float) ($est['total'] ?? 0), 1); ?></div>
        <div class="text-muted small">Estimasi total</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body">
        <div class="fs-3 fw-bold"><?php echo (int) ($report['ready'] ?? 0); ?>/35</div>
        <div class="text-muted small">Halaman siap (tanpa placeholder)</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body">
        <div class="fs-3 fw-bold"><?php echo number_format((float) ($est['content'] ?? 0), 1); ?></div>
        <div class="text-muted small">Aspek konten (60%)</div>
    </div></div></div>
    <div class="col-md-3"><div class="card text-center"><div class="card-body">
        <div class="fs-3 fw-bold"><?php echo number_format((float) ($est['design'] ?? 0) + (float) ($est['infra'] ?? 0), 1); ?></div>
        <div class="text-muted small">Desain + infrastruktur (40%)</div>
    </div></div></div>
</div>

<table class="table table-sm table-striped align-middle">
    <caption class="visually-hidden">Status 35 butir penilaian</caption>
    <thead><tr>
        <th scope="col">No</th><th scope="col">Butir</th><th scope="col">URL</th>
        <th scope="col">HTTP</th><th scope="col">Placeholder</th><th scope="col">Nilai</th>
    </tr></thead>
    <tbody>
    <?php foreach (($report['indicators'] ?? []) as $i => $row) : ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><code><?php echo htmlspecialchars((string) $row['url'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo (int) $row['status']; ?></td>
            <td><?php echo (int) $row['kosong']; ?></td>
            <td>
                <span class="badge bg-<?php echo $row['score'] === 10 ? 'success' : ($row['score'] === 5 ? 'warning text-dark' : 'danger'); ?>">
                    <?php echo (int) $row['score']; ?>
                </span>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
