<?php
declare(strict_types=1);

/**
 * Membangun pratinjau statis (tanpa Joomla) dari manifest.
 *
 *   php tools/preview.php
 *   → dist/preview/index.html + satu berkas per halaman indikator
 *
 * Tujuan: menyetujui arah visual dan struktur konten SEBELUM template
 * dipindahkan ke dalam Joomla. Halaman pratinjau memakai CSS yang sama
 * (packages/tpl_pa_satker/css/template.css) sehingga tampilan Joomla nanti
 * tidak berbeda jauh.
 */

require __DIR__ . '/../vendor/autoload.php';

use PA\Starter\Pure\Blocks;
use PA\Starter\Pure\IndicatorMap;

$root  = dirname(__DIR__);
$out   = $root . '/dist/preview';
$base  = rtrim(str_replace('\\', '/', $root), '/');
$site  = 'Pengadilan Agama Contoh';
$tahun = date('Y');

$map      = IndicatorMap::fromFile($root . '/manifest/indicators.json', $root . '/manifest/sections.json');
$sections = json_decode((string) file_get_contents($root . '/manifest/sections.json'), true, 512, JSON_THROW_ON_ERROR);
$indAll   = json_decode((string) file_get_contents($root . '/manifest/indicators.json'), true, 512, JSON_THROW_ON_ERROR)['indicators'];

$tokens = ['{{SITE_NAME}}' => $site, '{{TAHUN}}' => $tahun, '{{BASE}}' => ''];

/** '/profil/visi-misi' → 'profil-visi-misi.html' ; '/' → 'index.html' */
function fileOf(string $path): string
{
    $clean = trim($path, '/');

    return $clean === '' ? 'index.html' : str_replace('/', '-', $clean) . '.html';
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rrmkdir(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Tidak bisa membuat folder: {$dir}");
    }
}

function rcopy(string $src, string $dst): void
{
    if (is_file($src)) {
        rrmkdir(dirname($dst));
        copy($src, $dst);
        return;
    }
    rrmkdir($dst);
    foreach (scandir($src) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        rcopy($src . '/' . $item, $dst . '/' . $item);
    }
}

/* ---------- data navigasi ------------------------------------------------ */

$navItems = [];

$navItems[] = ['title' => 'Beranda', 'href' => 'index.html', 'children' => []];

foreach ($sections['sections'] as $section) {
    $children = [];

    foreach ($indAll as $ind) {
        if ($ind['section'] === $section['slug']) {
            $children[] = ['title' => $ind['title'], 'href' => fileOf(IndicatorMap::pathOf($ind))];
        }
    }

    foreach ($sections['extras'] as $extra) {
        if (($extra['section'] ?? '') === $section['slug']) {
            $children[] = ['title' => $extra['title'], 'href' => fileOf(IndicatorMap::pathOf($extra))];
        }
    }

    $navItems[] = [
        'title'    => $section['title'],
        'href'     => fileOf('/' . $section['alias']),
        'children' => $children,
    ];
}

/* ---------- kerangka halaman -------------------------------------------- */

function page(string $title, string $content, string $active, array $navItems, string $site, string $tahun, bool $home = false): string
{
    $nav = '';

    foreach ($navItems as $item) {
        $isActive = $item['href'] === $active ? ' is-active' : '';
        $hasKids  = $item['children'] !== [];

        $nav .= '<li class="' . trim($isActive) . '">';
        $nav .= '<a href="' . e($item['href']) . '"' . ($isActive ? ' aria-current="page"' : '') . '>' . e($item['title']) . '</a>';

        if ($hasKids) {
            $nav .= '<ul class="pa-submenu">';
            foreach ($item['children'] as $child) {
                $nav .= '<li><a href="' . e($child['href']) . '">' . e($child['title']) . '</a></li>';
            }
            $nav .= '</ul>';
        }

        $nav .= '</li>';
    }

    $year = e($tahun);

    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title} — {$site}</title>
<link rel="stylesheet" href="css/template.css">
<script src="js/template.js" defer></script>
</head>
<body class="pa">
<a class="pa-skip" href="#pa-main">Lewati ke konten utama</a>

<div style="background:#111;color:#ff9900;font:600 12px/1.6 system-ui;padding:.35rem 1rem;text-align:center">
PRATINJAU STATIS — data contoh, bukan situs Joomla. Tautan antarhalaman berfungsi.
</div>

<header class="pa-header">
  <div class="pa-topbar"><div class="pa-wrap">
    <div class="pa-topbar__meta">
      <span>Senin–Kamis 08.00–16.30 · Jumat 07.30–16.00 WIB</span>
      <span>info@pa-contoh.go.id</span>
      <span>Portal resmi (.go.id)</span>
    </div>
    <div class="pa-topbar__meta">
      <a href="#">SIWAS MARI</a>
      <a href="#">Instagram</a>
      <a href="#">YouTube</a>
    </div>
  </div></div>

  <div class="pa-brandbar"><div class="pa-wrap pa-brandbar__inner">
    <a class="pa-brand" href="index.html">
      <img src="images/logo/logo-ma.png" alt="Logo Mahkamah Agung Republik Indonesia" width="44" height="44">
      <img src="images/logo/logo-satker-placeholder.png" alt="Logo {$site}" width="44" height="44">
      <span class="pa-brand__text">
        <strong>{$site}</strong>
        <small>Mahkamah Agung Republik Indonesia</small>
      </span>
    </a>
    <button class="pa-navtoggle" type="button" aria-expanded="false" aria-controls="pa-nav">Menu</button>
    <form class="pa-search" role="search" action="#">
      <label class="visually-hidden" for="q">Cari informasi</label>
      <input id="q" name="q" type="search" placeholder="Cari jadwal sidang, LKjIP, panjar…">
      <button type="submit">Cari</button>
    </form>
  </div></div>

  <nav class="pa-nav" id="pa-nav" aria-label="Navigasi utama">
    <div class="pa-wrap"><ul class="pa-menu">{$nav}</ul></div>
  </nav>
</header>

{$content}

<footer class="pa-footer">
  <div class="pa-wrap">
    <div class="pa-footer__grid">
      <div>
        <h2>{$site}</h2>
        <p>Jl. Keadilan Nomor 1, Kelurahan Contoh, Kecamatan Contoh Utara, 12345.<br>
        Telepon (021) 1234567 · info@pa-contoh.go.id</p>
        <p>Kelas pengadilan: 1B · Wilayah hukum: 5 kecamatan</p>
      </div>
      <div>
        <h3>Layanan</h3>
        <ul>
          <li><a href="layanan-hukum-sipp.html">Penelusuran perkara (SIPP)</a></li>
          <li><a href="layanan-hukum-jadwal-sidang.html">Jadwal sidang</a></li>
          <li><a href="layanan-hukum-panjar-biaya.html">Panjar biaya</a></li>
          <li><a href="layanan-publik-ppid.html">Permohonan informasi</a></li>
        </ul>
      </div>
      <div>
        <h3>Tautan</h3>
        <ul>
          <li><a href="https://www.mahkamahagung.go.id">Mahkamah Agung RI</a></li>
          <li><a href="https://badilag.mahkamahagung.go.id">Ditjen Badan Peradilan Agama</a></li>
          <li><a href="https://putusan3.mahkamahagung.go.id">Direktori Putusan</a></li>
          <li><a href="https://siwas.mahkamahagung.go.id">SIWAS MARI</a></li>
        </ul>
      </div>
      <div>
        <h3>Statistik kunjungan</h3>
        <div class="pa-footer__stats">
          <div><span>Hari ini</span><strong>0</strong></div>
          <div><span>Bulan ini</span><strong>0</strong></div>
          <div><span>Total</span><strong>0</strong></div>
        </div>
        <p style="margin-top:.6rem;font-size:.8rem">Diisi otomatis modul Statistik Kunjungan.</p>
      </div>
    </div>
    <nav class="pa-footer__nav" aria-label="Navigasi footer">
      <ul>
        <li><a href="profil.html">Profil</a></li>
        <li><a href="layanan-hukum.html">Layanan Hukum</a></li>
        <li><a href="transparansi.html">Transparansi</a></li>
        <li><a href="layanan-publik.html">Layanan Publik</a></li>
        <li><a href="berita.html">Berita</a></li>
        <li><a href="peta-sinova.html">Peta Kesiapan SINOVA</a></li>
      </ul>
    </nav>
    <p class="pa-footer__sk">Standar Tata Kelola Website: SK Dirjen Badilag No. 2722/DJA/SK.TI1/X/2026 tanggal 12 Oktober 2026.</p>
  </div>
</footer>
<div class="pa-bottomline"><div class="pa-wrap"><span>© {$year} {$site}</span><span>Dikelola oleh Tim IT satuan kerja</span></div></div>
</body>
</html>
HTML;
}

/* ---------- beranda ------------------------------------------------------ */

$slides = [
    [
        'badge' => 'Portal pelayanan digital',
        'tag'   => 'h1',
        'title' => 'Pelayanan Cepat, Transparan, dan Akuntabel',
        'text'  => 'Informasi perkara, jadwal sidang, dan produk pengadilan dalam satu pintu.',
        'actions' => [
            ['Cek jadwal sidang', 'layanan-hukum-jadwal-sidang.html', 'pa-btn--secondary'],
            ['Rincian panjar', 'layanan-hukum-panjar-biaya.html', 'pa-btn--ghost'],
        ],
    ],
    [
        'badge' => 'Berperkara secara elektronik',
        'tag'   => 'h2',
        'title' => 'Daftar Perkara Lewat e-Court',
        'text'  => 'Pendaftaran, pembayaran panjar, dan persidangan elektronik tanpa antre.',
        'actions' => [
            ['Panduan e-Court', 'layanan-hukum-ecourt.html', 'pa-btn--secondary'],
        ],
    ],
    [
        'badge' => 'Peradilan inklusif',
        'tag'   => 'h2',
        'title' => 'Layanan Ramah Kelompok Rentan',
        'text'  => 'Fasilitas aksesibel, pendampingan, dan bantuan hukum cuma-cuma.',
        'actions' => [
            ['Lihat fasilitas layanan', 'layanan-publik-disabilitas.html', 'pa-btn--secondary'],
        ],
    ],
];

$hero = '';

foreach ($slides as $i => $slide) {
    $num = $i + 1;
    $tag = $slide['tag'];
    $buttons = '';
    foreach ($slide['actions'] as [$label, $href, $class]) {
        $buttons .= '<a class="pa-btn ' . e($class) . '" href="' . e($href) . '">' . e($label) . '</a>';
    }

    $hero .= <<<HTML
<div class="pa-hero__slide" style="background-image:url('images/slider/hero-{$num}.jpg')">
  <div class="pa-wrap pa-hero__inner">
    <span class="pa-hero__badge">{$slide['badge']}</span>
    <{$tag}>{$slide['title']}</{$tag}>
    <p>{$slide['text']}</p>
    <div class="pa-hero__actions">{$buttons}</div>
  </div>
</div>
HTML;
}

$shortcuts = [
    ['sipp', 'SIPP Satker', 'Penelusuran perkara dan jadwal sidang', 'layanan-hukum-sipp.html'],
    ['ecourt', 'e-Court MA RI', 'Pendaftaran dan persidangan elektronik', 'layanan-hukum-ecourt.html'],
    ['putusan', 'Direktori Putusan', 'Telusur putusan pengadilan', 'layanan-hukum-direktori-putusan.html'],
    ['siwas', 'SIWAS MARI', 'Pengaduan dan pengawasan', 'layanan-publik-rekapitulasi-pengaduan.html'],
];

$cardsShortcut = '';
foreach ($shortcuts as [$icon, $title, $text, $href]) {
    $cardsShortcut .= '<li class="pa-shortcut"><a href="' . e($href) . '">'
        . '<img src="images/ikon/' . $icon . '.svg" alt="" width="44" height="44">'
        . '<strong>' . e($title) . '</strong><span>' . e($text) . '</span></a></li>';
}

$news = [
    ['Sidang keliling di kecamatan terjauh', 'Pelayanan Masyarakat', 'images/slider/hero-1.jpg', 'layanan-hukum-jadwal-sidang.html'],
    ['Pendaftaran perkara daring lewat e-Court', 'Inovasi Layanan', 'images/slider/hero-2.jpg', 'layanan-hukum-ecourt.html'],
    ['Pendampingan bagi penyandang disabilitas', 'Layanan Inklusif', 'images/slider/hero-3.jpg', 'layanan-publik-disabilitas.html'],
    ['Penelusuran perkara lewat SIPP', 'Kepaniteraan', 'images/slider/hero-1.jpg', 'layanan-hukum-sipp.html'],
];

$newsHtml = '';
foreach ($news as [$title, $tag, $img, $href]) {
    $newsHtml .= '<li><article><img src="' . e($img) . '" alt="" loading="lazy">'
        . '<span class="pa-tag">' . e($tag) . '</span>'
        . '<h3><a href="' . e($href) . '">' . e($title) . '</a></h3>'
        . '<p>Contoh isi berita pelayanan. Ganti dengan berita satker yang berorientasi layanan.</p>'
        . '</article></li>';
}

$home = <<<HTML
<div class="pa-hero">
  <div class="pa-hero__slides">{$hero}</div>
  <div class="pa-hero__dots"><a href="#" aria-label="Slide 1"></a><a href="#" aria-label="Slide 2"></a><a href="#" aria-label="Slide 3"></a></div>
</div>

<section class="pa-section pa-section--white">
  <div class="pa-wrap">
    <div class="pa-section__head">
      <h2>Pintasan layanan utama</h2>
      <p>Akses langsung ke sistem informasi perkara, produk pengadilan, dan pengaduan.</p>
    </div>
    <ul class="pa-shortcuts">{$cardsShortcut}</ul>
  </div>
</section>

<section class="pa-section">
  <div class="pa-wrap pa-homegrid">
    <div class="pa-panel">
      <div class="pa-panel__head"><h2>Berita pelayanan &amp; inovasi</h2><a href="berita.html">Indeks berita</a></div>
      <ul class="pa-news">{$newsHtml}</ul>
    </div>
    <div class="pa-panel">
      <div class="pa-panel__head"><h3>Pengumuman</h3><a href="layanan-hukum-panggilan-ghaib.html">Semua</a></div>
      <ul class="pa-announce">
        <li><span class="pa-tag pa-tag--accent">Panggilan ghaib</span><a href="layanan-hukum-panggilan-ghaib.html">Daftar panggilan ghaib terbaru</a><time>Contoh</time></li>
        <li><span class="pa-tag">Pengadaan</span><a href="transparansi-pengadaan.html">Rencana umum pengadaan {$tahun}</a><time>Contoh</time></li>
        <li><span class="pa-tag">Layanan</span><a href="layanan-publik-laporan-akses-informasi.html">Laporan akses informasi publik</a><time>Contoh</time></li>
        <li><span class="pa-tag">Kinerja</span><a href="transparansi-lkjip.html">LKjIP dan dokumen SAKIP</a><time>Contoh</time></li>
      </ul>
    </div>
  </div>
</section>

<section class="pa-section pa-section--white">
  <div class="pa-wrap">
    <div class="pa-section__head">
      <h2>Enam menu wajib menurut SK 2722/2026</h2>
      <p>Beranda · Profil Pengadilan · Layanan Hukum · Transparansi · Layanan Publik &amp; Pengaduan · Berita &amp; Publikasi</p>
    </div>
    <p style="max-width:60rem;color:#56635e">Setiap butir penilaian punya satu URL tetap. Operator hanya mengisi dokumen dan tautan; struktur halaman tidak perlu diubah.</p>
  </div>
</section>
HTML;

rrmkdir($out);
file_put_contents($out . '/index.html', page('Beranda', $home, 'index.html', $navItems, $site, $tahun, true));

/* ---------- halaman seksi ------------------------------------------------ */

foreach ($sections['sections'] as $section) {
    $path    = '/' . $section['alias'];
    $title   = $section['title'];
    $content = Blocks::render($section['blocks'], $tokens);
    $html    = '<main id="pa-main" class="pa-main"><div class="pa-wrap">'
        . '<div class="pa-breadcrumb"><a href="index.html">Beranda</a> / ' . e($title) . '</div>'
        . '<div class="pa-content"><h1>' . e($title) . '</h1>'
        . '<p class="pa-intro">' . e(str_replace('{{SITE_NAME}}', $site, $section['intro'])) . '</p>'
        . $content . '</div></div></main>';

    file_put_contents($out . '/' . fileOf($path), page($title, $html, fileOf($path), $navItems, $site, $tahun));
}

/* ---------- halaman indikator + tambahan --------------------------------- */

$sideNav = static function (string $sectionSlug, string $currentFile) use ($sections, $indAll): string {
    $section = null;
    foreach ($sections['sections'] as $s) {
        if ($s['slug'] === $sectionSlug) {
            $section = $s;
        }
    }

    if ($section === null) {
        return '';
    }

    $html = '<aside class="pa-side"><nav class="pa-aside" aria-label="Isi bagian ' . e($section['title']) . '">'
        . '<h2>' . e($section['title']) . '</h2><ul>';

    foreach ($indAll as $ind) {
        if ($ind['section'] === $sectionSlug) {
            $file = fileOf(IndicatorMap::pathOf($ind));
            $html .= '<li><a href="' . e($file) . '"' . ($file === $currentFile ? ' aria-current="page"' : '') . '>'
                . e($ind['title']) . '</a></li>';
        }
    }
    foreach ($sections['extras'] as $extra) {
        if (($extra['section'] ?? '') === $sectionSlug) {
            $file = fileOf(IndicatorMap::pathOf($extra));
            $html .= '<li><a href="' . e($file) . '"' . ($file === $currentFile ? ' aria-current="page"' : '') . '>'
                . e($extra['title']) . '</a></li>';
        }
    }

    return $html . '</ul></nav></aside>';
};

$sinovaRows = '';
$n = 0;
foreach ($indAll as $ind) {
    $n++;
    $sinovaRows .= '<tr><td>' . $n . '</td><td>' . e(ucfirst($ind['aspect'])) . '</td><td>'
        . e(($ind['no'] ?? '') . ' ' . $ind['title']) . '</td><td><code>'
        . e(IndicatorMap::pathOf($ind)) . '</code></td><td>' . e($ind['criterion']) . '</td></tr>';
}
$petaHtml = '<div class="pa-table-wrap"><table class="pa-table"><caption>35 butir penilaian dan URL yang diinput ke SINOVA</caption>'
    . '<thead><tr><th>No</th><th>Aspek</th><th>Butir</th><th>Path</th><th>Kriteria</th></tr></thead><tbody>'
    . $sinovaRows . '</tbody></table></div>';

foreach (array_merge($indAll, $sections['extras']) as $row) {
    $path    = IndicatorMap::pathOf($row);
    $file    = fileOf($path);
    $title   = $row['title'];
    $content = Blocks::render($row['blocks'] ?? [], $tokens, ['sinova-table' => $petaHtml]);

    $meta = isset($row['criterion'])
        ? '<div class="pa-note"><span class="pa-note__label">Butir penilaian</span><p>'
          . e($row['no'] . ' — ' . $title . ' · ' . ($row['aspect'] === 'berkala' ? 'Informasi berkala' : 'Informasi wajib'))
          . '<br>Bukti minimum: ' . e($row['criterion'])
          . '<br>URL SINOVA: <code>' . e($path) . '</code></p></div>'
        : '';

    $side = isset($row['section']) ? $sideNav($row['section'], $file) : '';

    $crumb = '<div class="pa-breadcrumb"><a href="index.html">Beranda</a> / '
        . (str_starts_with($path, '/profil') ? '<a href="profil.html">Profil Pengadilan</a> / ' : '')
        . (str_starts_with($path, '/layanan-hukum') ? '<a href="layanan-hukum.html">Layanan Hukum</a> / ' : '')
        . (str_starts_with($path, '/transparansi') ? '<a href="transparansi.html">Transparansi</a> / ' : '')
        . (str_starts_with($path, '/layanan-publik') ? '<a href="layanan-publik.html">Layanan Publik</a> / ' : '')
        . (str_starts_with($path, '/berita') ? '<a href="berita.html">Berita</a> / ' : '')
        . e($title) . '</div>';

    $main = '<main id="pa-main" class="pa-main"><div class="pa-wrap">' . $crumb
        . '<div class="pa-layout ' . ($side !== '' ? 'pa-layout--side' : 'pa-layout--full') . '">'
        . '<div class="pa-content"><h1>' . e($title) . '</h1>' . $meta . $content . '</div>'
        . $side
        . '</div></div></main>';

    file_put_contents($out . '/' . $file, page($title, $main, $file, $navItems, $site, $tahun));
}

/* ---------- aset --------------------------------------------------------- */

rcopy($root . '/packages/tpl_pa_satker/css/template.css', $out . '/css/template.css');
rcopy($root . '/packages/tpl_pa_satker/js/template.js', $out . '/js/template.js');
rcopy($root . '/assets/slider', $out . '/images/slider');
rcopy($root . '/assets/ikon', $out . '/images/ikon');
rcopy($root . '/assets/flyer', $out . '/images/flyer');
rcopy($root . '/assets/logo', $out . '/images/logo');
rcopy($root . '/assets/og', $out . '/images/og');

$pages = glob($out . '/*.html') ?: [];
printf("Pratinjau dibuat: %d halaman\n%s\n", count($pages), $out . '/index.html');
