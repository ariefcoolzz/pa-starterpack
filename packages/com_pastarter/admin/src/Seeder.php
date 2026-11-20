<?php
declare(strict_types=1);

namespace PA\Starter\Administrator;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Content;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Menu;
use Joomla\CMS\Table\Module;
use Joomla\Database\DatabaseInterface;
use PA\Starter\Administrator\Pure\Blocks;
use Psr\Container\ContainerInterface;

defined('_JEXEC') or die;

/**
 * Menyemai konten awal: kategori → artikel → menu (nested set) → modul.
 *
 * Idempoten: kunci pencarian artikel (alias+kategori), menu (menutype+parent+alias),
 * modul (module+position+title). Jalan kedua harus melaporkan "dibuat 0".
 * Seeder hanya MEMBUAT, tidak pernah menimpa konten yang sudah ada.
 */
final class Seeder
{
    private DatabaseInterface $db;

    /** @var array{dibuat:int, dilewati:int, gagal:int} */
    private array $log = ['dibuat' => 0, 'dilewati' => 0, 'gagal' => 0];

    /** @var array<string, mixed> */
    private array $indicators = [];

    /** @var array<string, mixed> */
    private array $sections = [];

    /** @var array<int, array<string, string>> */
    private array $catIds = [];

    private int $comContentId = 0;

    private string $manifestDir = '';

    public function __construct(private readonly ContainerInterface $container)
    {
        $this->db = $container->get(DatabaseInterface::class);
    }

    /** @param array<int, string> $argv */
    public function run(array $argv = []): array
    {
        $this->loadManifest();
        $this->comContentId = $this->componentId('com_content');

        $this->seedCategories();
        $this->seedSectionLandings();
        $this->seedIndicators();
        $this->seedExtras();
        $this->seedNews();
        $this->seedMenus();
        $this->seedModules();
        $this->setTemplateStyle();
        $this->enablePlugins();

        return $this->log;
    }

    private function loadManifest(): void
    {
        $env = getenv('PA_MANIFEST');
        $dir = $env && is_dir($env)
            ? rtrim($env, '/\\')
            : JPATH_ADMINISTRATOR . '/components/com_pastarter/manifest';

        foreach (['indicators.json', 'sections.json'] as $berkas) {
            if (!is_file($dir . '/' . $berkas)) {
                throw new \RuntimeException("Manifest tidak ditemukan: {$dir}/{$berkas}");
            }
        }

        $this->manifestDir = $dir;
        $ind = json_decode((string) file_get_contents($dir . '/indicators.json'), true, 512, JSON_THROW_ON_ERROR);
        $sec = json_decode((string) file_get_contents($dir . '/sections.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->indicators = $ind['indicators'];
        $this->sections   = $sec;
    }

    private function tokens(): array
    {
        return [
            '{{SITE_NAME}}' => Factory::getApplication()->get('sitename', 'Pengadilan Agama'),
            '{{TAHUN}}'     => date('Y'),
            '{{BASE}}'      => \Joomla\CMS\Uri\Uri::root(true) . '/',
        ];
    }

    private function componentId(string $element): int
    {
        // Komponen tersimpan di #__extensions dengan client_id = 1 (sisi administrator).
        $q = $this->db->getQuery(true)
            ->select($this->db->quoteName('extension_id'))
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('element') . ' = :el')
            ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
            ->bind(':el', $element);
        $this->db->setQuery($q);

        return (int) $this->db->loadResult();
    }

    private function seedCategories(): void
    {
        foreach ($this->sections['categories'] as $cat) {
            $alias = 'kategori-' . $cat['slug'];
            $table = new Category($this->db);
            $table->load(['alias' => $alias, 'extension' => 'com_content']);

            if ($table->id) {
                $this->catIds[$cat['slug']] = (int) $table->id;
                $this->log['dilewati']++;
                continue;
            }

            $table->bind([
                'title'     => $cat['title'],
                'alias'     => $alias,
                'extension' => 'com_content',
                'parent_id' => 1,
                'published' => 1,
                'access'    => 1,
                'language'  => '*',
                'params'    => '{}',
                'metadata'  => '{}',
            ]);

            if (!$table->check() || !$table->store()) {
                throw new \RuntimeException('Kategori gagal: ' . $cat['title'] . ' — ' . $table->getError());
            }

            $this->catIds[$cat['slug']] = (int) $table->id;
            $this->log['dibuat']++;
        }
    }

    private function seedSectionLandings(): void
    {
        foreach ($this->sections['sections'] as $section) {
            $body = Blocks::render($section['blocks'] ?? [], $this->tokens());
            $intro = '<p class="pa-intro">' . htmlspecialchars(
                strtr($section['intro'] ?? '', $this->tokens()),
                ENT_QUOTES,
                'UTF-8'
            ) . '</p>';

            $this->createArticle('halaman', $section['alias'], $section['title'], $intro . $body);
        }
    }

    private function seedIndicators(): void
    {
        foreach ($this->indicators as $row) {
            $body = Blocks::render($row['blocks'] ?? [], $this->tokens());
            $slug = $row['section'] === '_root' ? 'dokumen' : $row['section'];
            $this->createArticle($slug, $row['path'], $row['title'], $body);
        }
    }

    private function seedExtras(): void
    {
        foreach ($this->sections['extras'] as $extra) {
            $body  = Blocks::render($extra['blocks'] ?? [], $this->tokens());
            $slug  = $extra['section'] === '_root' ? 'dokumen' : $extra['section'];
            $this->createArticle($slug, $extra['path'], $extra['title'], $body);
        }
    }

    private function seedNews(): void
    {
        $berita = [
            [
                'Sidang keliling di kecamatan terjauh',
                'sidang-keliling-kecamatan-terjauh',
                'images/konten/ruang-ptsp-640.jpg',
                'Pelayanan sidang di luar gedung mendekatkan akses keadilan bagi masyarakat yang terkendala jarak dan biaya. Jadwal sidang keliling diumumkan melalui papan pengumuman dan SIPP.',
            ],
            [
                'Mediasi sebagai jalur utama penyelesaian sengketa',
                'mediasi-jalur-utama-penyelesaian-sengketa',
                'images/konten/ruang-mediasi-640.jpg',
                'Setiap perkara perdata wajib menempuh mediasi sebelum pemeriksaan pokok perkara. Mediator bersertifikat membantu para pihak menemukan kesepakatan yang adil dan tahan lama.',
            ],
            [
                'Layanan prioritas bagi penyandang disabilitas',
                'layanan-prioritas-penyandang-disabilitas',
                'images/konten/ruang-ptsp-640.jpg',
                'Pengadilan menyediakan jalur pemandu, kursi roda, ruang tunggu prioritas, dan petugas pendamping. Sampaikan kebutuhan khusus saat mendaftar di PTSP.',
            ],
            [
                'Penelusuran perkara lewat SIPP',
                'penelusuran-perkara-lewat-sipp',
                'images/konten/gedung-kantor-640.jpg',
                'Masyarakat dapat memantau status perkara, jadwal sidang, dan putusan melalui Sistem Informasi Penelusuran Perkara tanpa datang ke pengadilan.',
            ],
        ];

        foreach ($berita as [$title, $alias, $image, $intro]) {
            $body = '<p>' . $intro . '</p>'
                . '<p>Contoh isi berita. Ganti dengan berita kegiatan pelayanan satker, lengkapi tanggal dan dokumentasi.</p>';
            $this->createArticle('berita', $alias, $title, $body, $image);
        }

        $pengumuman = [
            ['Pengumuman jadwal sidang pekan ini', 'pengumuman-jadwal-sidang-pekan-ini', 'Jadwal sidang terbaru dapat dilihat pada halaman Jadwal Persidangan dan SIPP satker.'],
            ['Rencana Umum Pengadaan tahun berjalan', 'pengumuman-rencana-umum-pengadaan', 'RUP dapat diakses melalui halaman Pengadaan Barang dan Jasa serta SIRUP LKPP.'],
        ];

        foreach ($pengumuman as [$title, $alias, $intro]) {
            $this->createArticle('pengumuman', $alias, $title, '<p>' . $intro . '</p>');
        }
    }

    private function createArticle(string $catSlug, string $alias, string $title, string $html, string $image = ''): void
    {
        $catSlug = $catSlug === '_root' ? 'dokumen' : $catSlug;
        $catId   = $this->catIds[$catSlug] ?? 0;

        if ($catId === 0) {
            throw new \RuntimeException("Kategori tidak dikenal: {$catSlug}");
        }

        $table = new Content($this->db);
        // Alias diperlakukan unik global agar seed ulang tidak membuat duplikat
        // ketika artikel dipindah kategori (mis. landing ke kategori "halaman").
        $table->load(['alias' => $alias]);

        if ($table->id) {
            $this->log['dilewati']++;

            return;
        }

        $now = Factory::getDate()->toSql();
        $images = json_encode([
            'image_intro'            => $image,
            'float_intro'            => '',
            'image_intro_alt'        => $image !== '' ? $title : '',
            'image_intro_caption'    => '',
            'image_fulltext'         => '',
            'float_fulltext'         => '',
            'image_fulltext_alt'     => '',
            'image_fulltext_caption' => '',
        ], JSON_THROW_ON_ERROR);

        $table->bind([
            'title'        => $title,
            'alias'        => $alias,
            'introtext'    => '',
            'fulltext'     => $html,
            'catid'        => $catId,
            'state'        => 1,
            'access'       => 1,
            'language'     => '*',
            'created'      => $now,
            'created_by'   => (int) (Factory::getApplication()->getIdentity()->id ?? 0),
            'modified'     => $now,
            'publish_up'   => $now,
            'publish_down' => null,
            'images'       => $images,
            'urls'         => '{}',
            'attribs'      => '{}',
            'metadata'     => '{}',
            'metadesc'     => '',
            'metakey'      => '',
            'version'      => 1,
            'ordering'     => 0,
            'featured'     => 0,
            'hits'         => 0,
        ]);

        if (!$table->check() || !$table->store()) {
            throw new \RuntimeException('Artikel gagal: ' . $title . ' — ' . $table->getError());
        }

        $this->log['dibuat']++;
    }

    private function articleId(string $catSlug, string $alias): int
    {
        $catSlug = $catSlug === '_root' ? 'dokumen' : $catSlug;
        $q = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__content'))
            ->where($this->db->quoteName('alias') . ' = :al')
            ->where($this->db->quoteName('catid') . ' = :ci')
            ->bind(':al', $alias)
            ->bind(':ci', $this->catIds[$catSlug], \Joomla\Database\ParameterType::INTEGER);
        $this->db->setQuery($q);

        return (int) $this->db->loadResult();
    }

    private function ensureMenuType(string $menutype, string $title): void
    {
        $q = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__menu_types'))
            ->where($this->db->quoteName('menutype') . ' = :mt')
            ->bind(':mt', $menutype);
        $this->db->setQuery($q);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $obj = (object) ['menutype' => $menutype, 'title' => $title, 'description' => 'Dibuat oleh PA Starter'];
        $this->db->insertObject('#__menu_types', $obj);
    }

    private function seedMenus(): void
    {
        $this->ensureMenuType('hidden', 'Halaman tersembunyi');
        $this->ensureMenuType('footer', 'Navigasi footer');

        // Menu contoh bawaan Joomla ("main") dibuang agar navigasi hanya berisi 6 menu SK.
        $this->db->setQuery('DELETE FROM ' . $this->db->quoteName('#__menu')
            . ' WHERE ' . $this->db->quoteName('menutype') . ' = ' . $this->db->quote('main'))->execute();
        $this->db->setQuery('DELETE FROM ' . $this->db->quoteName('#__menu_types')
            . ' WHERE ' . $this->db->quoteName('menutype') . ' = ' . $this->db->quote('main'))->execute();

        $root = 1;

        // Tepat satu item home.
        $this->db->setQuery('UPDATE ' . $this->db->quoteName('#__menu')
            . ' SET ' . $this->db->quoteName('home') . ' = 0 WHERE ' . $this->db->quoteName('home') . ' = 1')->execute();

        // Beranda (home)
        $homeId = $this->menuItem([
            'menutype' => 'mainmenu',
            'title'    => 'Beranda',
            'alias'    => 'beranda',
            'link'     => 'index.php?option=com_content&view=featured',
            'parent_id' => $root,
            'home'     => 1,
        ]);

        // Pastikan penanda home menempel pada item ini (bisa sudah ada dari seed sebelumnya).
        $this->db->setQuery('UPDATE ' . $this->db->quoteName('#__menu')
            . ' SET ' . $this->db->quoteName('home') . ' = 1'
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . (int) $homeId)->execute();
        $this->db->setQuery('UPDATE ' . $this->db->quoteName('#__menu')
            . ' SET ' . $this->db->quoteName('home') . ' = 0'
            . ' WHERE ' . $this->db->quoteName('id') . ' <> ' . (int) $homeId
            . ' AND ' . $this->db->quoteName('home') . ' = 1')->execute();

        // Seksi + indikator. Seksi menjadi anak akar agar URL = /<seksi>/<butir>,
        // bukan /beranda/<seksi>/<butir>.
        foreach ($this->sections['sections'] as $section) {
            $landingId = $this->articleId($section['slug'], $section['alias']);

            $sectionMenuId = $this->menuItem([
                'menutype'  => 'mainmenu',
                'title'     => $section['title'],
                'alias'     => $section['alias'],
                'link'      => 'index.php?option=com_content&view=article&id=' . $landingId,
                'parent_id' => $root,
            ]);

            foreach ($this->indicators as $row) {
                if ($row['section'] !== $section['slug']) {
                    continue;
                }

                $id = $this->articleId($row['section'], $row['path']);
                $this->menuItem([
                    'menutype'  => 'mainmenu',
                    'title'     => $row['title'],
                    'alias'     => $row['path'],
                    'link'      => 'index.php?option=com_content&view=article&id=' . $id,
                    'parent_id' => $sectionMenuId,
                ]);
            }

            foreach ($this->sections['extras'] as $extra) {
                if (($extra['section'] ?? '') !== $section['slug']) {
                    continue;
                }

                $id = $this->articleId($extra['section'], $extra['path']);
                $this->menuItem([
                    'menutype'  => 'mainmenu',
                    'title'     => $extra['title'],
                    'alias'     => $extra['path'],
                    'link'      => 'index.php?option=com_content&view=article&id=' . $id,
                    'parent_id' => $sectionMenuId,
                ]);
            }
        }

        // Halaman tersembunyi tanpa induk: indikator & tambahan ber-section _root
        foreach ($this->indicators as $row) {
            if ($row['section'] !== '_root') {
                continue;
            }

            $id = $this->articleId('dokumen', $row['path']);
            $this->menuItem([
                'menutype'  => 'hidden',
                'title'     => $row['title'],
                'alias'     => $row['path'],
                'link'      => 'index.php?option=com_content&view=article&id=' . $id,
                'parent_id' => $root,
            ]);
        }

        foreach ($this->sections['extras'] as $extra) {
            if (($extra['section'] ?? '') !== '_root') {
                continue;
            }

            $id = $this->articleId('dokumen', $extra['path']);
            $this->menuItem([
                'menutype'  => 'hidden',
                'title'     => $extra['title'],
                'alias'     => $extra['path'],
                'link'      => 'index.php?option=com_content&view=article&id=' . $id,
                'parent_id' => $root,
            ]);
        }

        // Footer: tautan seksi. Alias diberi awalan karena Joomla memeriksa
        // keunikan alias per parent tanpa melihat menutype.
        foreach ($this->sections['sections'] as $section) {
            $landingId = $this->articleId($section['slug'], $section['alias']);
            $this->menuItem([
                'menutype'  => 'footer',
                'title'     => $section['title'],
                'alias'     => 'f-' . $section['alias'],
                'link'      => 'index.php?option=com_content&view=article&id=' . $landingId,
                'parent_id' => $root,
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function menuItem(array $data): int
    {
        $table = new Menu($this->db);
        $table->load([
            'menutype'  => $data['menutype'],
            'alias'     => $data['alias'],
            'parent_id' => (int) $data['parent_id'],
            'client_id' => 0,
        ]);

        if ($table->id) {
            $this->log['dilewati']++;

            return (int) $table->id;
        }

        $table->bind([
            'menutype'          => $data['menutype'],
            'title'             => $data['title'],
            'alias'             => $data['alias'],
            'note'              => '',
            'link'              => $data['link'],
            'type'              => 'component',
            'published'         => 1,
            'parent_id'         => (int) $data['parent_id'],
            'component_id'      => $this->comContentId,
            'access'            => 1,
            'img'               => '',
            'template_style_id' => 0,
            'params'            => '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}',
            'home'              => (int) ($data['home'] ?? 0),
            'language'          => '*',
            'client_id'         => 0,
            'browserNav'        => 0,
        ]);

        // Nested set: wajib sebelum store, kalau tidak path SEF tidak terbentuk.
        $table->setLocation((int) $data['parent_id'], 'last-child');

        // J6: kolom path tidak punya nilai default; hitung dari induk.
        if (empty($table->path)) {
            $parentPath = '';

            if ((int) $data['parent_id'] > 1) {
                $q = $this->db->getQuery(true)
                    ->select($this->db->quoteName('path'))
                    ->from($this->db->quoteName('#__menu'))
                    ->where($this->db->quoteName('id') . ' = :pid')
                    ->bind(':pid', $data['parent_id'], \Joomla\Database\ParameterType::INTEGER);
                $this->db->setQuery($q);
                $parentPath = (string) $this->db->loadResult();
            }

            $table->path = ltrim($parentPath . '/' . $table->alias, '/');
        }

        if (!$table->store()) {
            throw new \RuntimeException('Menu gagal: ' . $data['title'] . ' — ' . $table->getError());
        }

        $this->log['dibuat']++;

        return (int) $table->id;
    }

    private function seedModules(): void
    {
        $tokens = $this->tokens();

        $heroFile = $this->manifestDir . '/hero.html';
        $hero     = is_file($heroFile) ? strtr((string) file_get_contents($heroFile), $tokens) : '';

        $shortcutFile = $this->manifestDir . '/shortcuts.html';
        $shortcut     = is_file($shortcutFile) ? strtr((string) file_get_contents($shortcutFile), $tokens) : '';

        $modul = [
            ['Menu Utama', 'mod_menu', 'mainnav', 1, ['menutype' => 'mainmenu', 'startLevel' => 1, 'endLevel' => 0, 'showAllChildren' => 1], 0],
            ['Pintasan Layanan', 'mod_custom', 'home-shortcut', 2, [], 0, $shortcut],
            ['Hero Beranda', 'mod_custom', 'home-top', 1, [], 0, $hero],
            ['Berita Terkini', 'mod_articles_category', 'home-center', 1, [
                'count' => 4, 'catid' => [$this->catIds['berita'] ?? 1], 'show_introtext' => 1,
                'introtext_limit' => 130, 'show_date' => 'published', 'date_format' => 'd F Y',
                'article_ordering' => 'a.publish_up', 'article_ordering_direction' => 'DESC',
                'show_category' => 1, 'link_titles' => 1, 'show_images' => 1,
            ], 1],
            ['Pengumuman', 'mod_articles_category', 'home-side', 1, [
                'count' => 4, 'catid' => [$this->catIds['pengumuman'] ?? 1], 'show_introtext' => 0,
                'show_date' => 'published', 'date_format' => 'd F Y',
                'article_ordering' => 'a.publish_up', 'article_ordering_direction' => 'DESC',
                'show_category' => 0, 'link_titles' => 1,
            ], 1],
            ['Navigasi Footer', 'mod_menu', 'footer-nav', 1, ['menutype' => 'footer', 'startLevel' => 1, 'endLevel' => 1], 0],
            ['Identitas Footer', 'mod_custom', 'footer-1', 1, [], 0,
                '<h2>' . htmlspecialchars($tokens['{{SITE_NAME}}'], ENT_QUOTES, 'UTF-8') . '</h2>'
                . '<p>Jl. Keadilan Nomor 1, Kelurahan Contoh, Kecamatan Contoh Utara, 12345.<br>'
                . 'Telepon (021) 1234567 · info@pa-contoh.go.id</p>'],
            ['Hak Cipta', 'mod_footer', 'footer', 1, [], 0],
        ];

        foreach ($modul as $row) {
            $name     = $row[0];
            $module   = $row[1];
            $position = $row[2];
            $ordering = $row[3];
            $params   = $row[4];
            $showTitle = $row[5];
            $content  = $row[6] ?? null;

            $table = new Module($this->db);
            $table->load(['module' => $module, 'position' => $position, 'client_id' => 0]);

            if ($table->id) {
                $this->log['dilewati']++;
                $this->publishModuleEverywhere((int) $table->id);
                continue;
            }

            $bind = [
                'title'     => $name,
                'module'    => $module,
                'position'  => $position,
                'ordering'  => $ordering,
                'published' => 1,
                'access'    => 1,
                'language'  => '*',
                'client_id' => 0,
                'params'    => json_encode($params),
                'showtitle' => $showTitle,
            ];

            if ($content !== null) {
                $bind['content'] = $content;
            }

            $table->bind($bind);

            if (!$table->store()) {
                throw new \RuntimeException('Modul gagal: ' . $name . ' — ' . $table->getError());
            }

            $this->publishModuleEverywhere((int) $table->id);
            $this->log['dibuat']++;
        }
    }

    private function publishModuleEverywhere(int $moduleId): void
    {
        $q = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__modules_menu'))
            ->where($this->db->quoteName('moduleid') . ' = :mid')
            ->bind(':mid', $moduleId, \Joomla\Database\ParameterType::INTEGER);
        $this->db->setQuery($q);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__modules_menu')
            . ' (' . $this->db->quoteName('moduleid') . ', ' . $this->db->quoteName('menuid') . ')'
            . ' VALUES (' . $moduleId . ', 0)'
        )->execute();
    }

    private function setTemplateStyle(): void
    {
        $this->db->setQuery(
            'UPDATE ' . $this->db->quoteName('#__template_styles')
            . ' SET ' . $this->db->quoteName('home') . ' = (CASE WHEN '
            . $this->db->quoteName('template') . " = 'pa_satker' THEN 1 ELSE 0 END)"
        )->execute();
    }

    private function enablePlugins(): void
    {
        $daftar = [
            ['system', 'accessibility'],
            ['system', 'skipto'],
            ['system', 'httpheaders'],
            ['system', 'finder'],
            ['system', 'sef'],
            ['content', 'loadmodule'],
        ];

        foreach ($daftar as [$folder, $element]) {
            $table = new Extension($this->db);
            $table->load(['type' => 'plugin', 'folder' => $folder, 'element' => $element, 'client_id' => 0]);

            if (!$table->extension_id || (int) $table->enabled === 1) {
                continue;
            }

            $table->enabled = 1;
            $table->store();
        }
    }
}
