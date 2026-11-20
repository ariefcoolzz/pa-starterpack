<?php
/**
 * Template pa_satker — Joomla 6.x
 *
 * Identitas visual Peradilan Agama: primer #056e4f, sekunder #ff9900
 * (SK Dirjen Badilag 2722/DJA/SK.TI1/X/2026 Bab III.B.1).
 *
 * Catatan J6: tidak ada JFactory/JUri/JHtml. `$this` di sini adalah dokumen.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app    = Factory::getApplication();
$wa     = $this->getWebAssetManager();
$params = $this->params;

$wa->useStyle('pa.template');

$siteName  = $app->get('sitename', 'Pengadilan Agama');
$brandLine = $params->get('brand_line', 'Mahkamah Agung Republik Indonesia');
$skLine    = $params->get('sk_line', 'Standar Tata Kelola Website: SK Dirjen Badilag No. 2722/DJA/SK.TI1/X/2026.');
$logoMa    = $params->get('logo_ma', 'images/logo/logo-ma.png');
$logoSat   = $params->get('logo_satker', 'images/logo/logo-satker-placeholder.png');
$jam       = $params->get('jam_layanan', 'Senin–Kamis 08.00–16.30 · Jumat 07.30–16.00 WIB');
$showSkip  = (int) $params->get('show_skiplink', 1);

$root  = Uri::root(true);
$email = $app->get('mailfrom', '');
$active = $app->getMenu()->getActive();
$isHome = $app->getInput()->getCmd('view') === 'featured'
    || $app->getInput()->getCmd('view') === ''
    || ($active && (int) $active->home === 1);

$ada = function (string $pos): bool {
    return $this->countModules($pos, true) > 0;
};
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
</head>
<body class="pa <?php echo $isHome ? 'pa--home' : 'pa--inner'; ?>">
<?php if ($showSkip) : ?>
    <a class="pa-skip" href="#pa-main">Lewati ke konten utama</a>
<?php endif; ?>

<header class="pa-header">
    <div class="pa-topbar"><div class="pa-wrap">
        <div class="pa-topbar__meta">
            <span><?php echo htmlspecialchars($jam, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($email) : ?>
                <span><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <span>Portal resmi (.go.id)</span>
        </div>
        <?php if ($ada('topbar')) : ?>
            <div class="pa-topbar__meta"><jdoc:include type="modules" name="topbar" style="paPlain" /></div>
        <?php endif; ?>
    </div></div>

    <div class="pa-brandbar"><div class="pa-wrap pa-brandbar__inner">
        <a class="pa-brand" href="<?php echo $root; ?>/">
            <img src="<?php echo $root . '/' . ltrim((string) $logoMa, '/'); ?>" alt="Logo Mahkamah Agung Republik Indonesia" width="44" height="44">
            <?php if ($logoSat) : ?>
                <img src="<?php echo $root . '/' . ltrim((string) $logoSat, '/'); ?>" alt="Logo <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>" width="44" height="44">
            <?php endif; ?>
            <span class="pa-brand__text">
                <strong><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></strong>
                <small><?php echo htmlspecialchars($brandLine, ENT_QUOTES, 'UTF-8'); ?></small>
            </span>
        </a>

        <button class="pa-navtoggle" type="button" aria-expanded="false" aria-controls="pa-nav">Menu</button>

        <form class="pa-search" role="search" action="<?php echo $root; ?>/index.php" method="get">
            <label class="visually-hidden" for="pa-q">Cari informasi</label>
            <input id="pa-q" name="q" type="search" placeholder="Cari informasi…" required>
            <input type="hidden" name="option" value="com_finder">
            <input type="hidden" name="view" value="search">
            <button type="submit">Cari</button>
        </form>
    </div></div>

    <?php if ($ada('mainnav')) : ?>
        <nav class="pa-nav" id="pa-nav" aria-label="Navigasi utama">
            <div class="pa-wrap"><jdoc:include type="modules" name="mainnav" style="paPlain" /></div>
        </nav>
    <?php endif; ?>
</header>

<?php if ($isHome && $ada('home-top')) : ?>
    <div class="pa-wrap pa-hero-wrap"><div class="pa-hero"><jdoc:include type="modules" name="home-top" style="paPlain" /></div></div>
<?php endif; ?>

<main id="pa-main" class="pa-main">
    <div class="pa-wrap">
        <?php if ($ada('breadcrumbs') && !$isHome) : ?>
            <div class="pa-breadcrumb"><jdoc:include type="modules" name="breadcrumbs" style="paPlain" /></div>
        <?php endif; ?>

        <?php if ($ada('content-top')) : ?>
            <div class="pa-content-top"><jdoc:include type="modules" name="content-top" style="paPlain" /></div>
        <?php endif; ?>

        <?php if ($isHome) : ?>
            <?php if ($ada('home-quick')) : ?>
                <section class="pa-section pa-section--white pa-section--quick">
                    <jdoc:include type="modules" name="home-quick" style="paPlain" />
                </section>
            <?php endif; ?>

            <section class="pa-section">
                <div class="pa-section__head">
                    <div>
                        <h2>Berita terkini</h2>
                        <p>Kegiatan pelayanan, inovasi, dan informasi terbaru dari satuan kerja.</p>
                    </div>
                    <a class="pa-more" href="<?php echo $root; ?>/berita">Berita lain</a>
                </div>
                <div class="pa-homegrid">
                    <div class="pa-homegrid__main"><jdoc:include type="modules" name="home-center" style="paPlain" /></div>
                    <div class="pa-homegrid__side"><jdoc:include type="modules" name="home-side" style="paCard" /></div>
                </div>
            </section>

            <?php if ($ada('home-strip')) : ?>
                <section class="pa-section pa-section--white">
                    <div class="pa-section__head">
                        <div>
                            <h2>Layanan yang sering dicari</h2>
                            <p>Empat pintu masuk utama bagi masyarakat pencari keadilan.</p>
                        </div>
                    </div>
                    <jdoc:include type="modules" name="home-strip" style="paPlain" />
                </section>
            <?php endif; ?>

            <?php if ($ada('home-welcome')) : ?>
                <section class="pa-section">
                    <jdoc:include type="modules" name="home-welcome" style="paPlain" />
                </section>
            <?php endif; ?>
        <?php else : ?>
            <div class="pa-layout <?php echo $ada('sidebar') ? 'pa-layout--side' : 'pa-layout--full'; ?>">
                <div class="pa-content">
                    <jdoc:include type="message" />
                    <jdoc:include type="component" />
                </div>
                <?php if ($ada('sidebar')) : ?>
                    <aside class="pa-side" aria-label="Informasi bagian">
                        <jdoc:include type="modules" name="sidebar" style="paCard" />
                    </aside>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($ada('bottom')) : ?>
            <div class="pa-bottom"><jdoc:include type="modules" name="bottom" style="paCard" /></div>
        <?php endif; ?>
    </div>
</main>

<footer class="pa-footer">
    <div class="pa-wrap">
        <div class="pa-footer__grid">
            <div><jdoc:include type="modules" name="footer-1" style="paPlain" /></div>
            <div><jdoc:include type="modules" name="footer-2" style="paPlain" /></div>
            <div><jdoc:include type="modules" name="footer-3" style="paPlain" /></div>
            <div><jdoc:include type="modules" name="footer-4" style="paPlain" /></div>
        </div>
        <?php if ($ada('footer-nav')) : ?>
            <nav class="pa-footer__nav" aria-label="Navigasi footer">
                <jdoc:include type="modules" name="footer-nav" style="paPlain" />
            </nav>
        <?php endif; ?>
        <p class="pa-footer__sk"><?php echo htmlspecialchars($skLine, ENT_QUOTES, 'UTF-8'); ?></p>
        <jdoc:include type="modules" name="footer" style="paPlain" />
    </div>
</footer>

<div class="pa-a11y">
    <div class="pa-a11y__panel" id="pa-a11y-panel" hidden role="group" aria-label="Sarana aksesibilitas">
        <h2>Sarana aksesibilitas</h2>
        <div class="pa-a11y__row"><span>Ukuran teks</span>
            <span class="pa-a11y__group">
                <button type="button" data-a11y="font" data-tahap="0" aria-pressed="true" aria-label="Ukuran teks normal">A</button>
                <button type="button" data-a11y="font" data-tahap="1" aria-pressed="false" aria-label="Perbesar teks satu langkah">A+</button>
                <button type="button" data-a11y="font" data-tahap="2" aria-pressed="false" aria-label="Perbesar teks dua langkah">A++</button>
            </span>
        </div>
        <button type="button" data-a11y="contrast" aria-pressed="false">Kontras tinggi</button>
        <button type="button" data-a11y="garis" aria-pressed="false">Garis bawah tautan</button>
        <button type="button" data-a11y="reset">Atur ulang tampilan</button>
        <p class="pa-a11y__hint">Struktur halaman sudah ramah pembaca layar. Gunakan Tab untuk berpindah antarelemen.</p>
    </div>
    <button type="button" class="pa-a11y__toggle" data-a11y="open" aria-expanded="false" aria-controls="pa-a11y-panel">Aksesibilitas</button>
</div>

<div class="pa-bottomline"><div class="pa-wrap">
    <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
    <span>Dikelola oleh Tim IT satuan kerja</span>
</div></div>

<?php
// Skrip dimuat langsung dengan penanda versi berkas: aset type "script" di
// joomla.asset.json tidak selalu tercetak oleh useScript() pada J6.
$js = JPATH_ROOT . '/templates/pa_satker/js/template.js';
$ver = is_file($js) ? filemtime($js) : 1;
?>
<script src="<?php echo $root; ?>/templates/pa_satker/js/template.js?v=<?php echo $ver; ?>" defer></script>

<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
