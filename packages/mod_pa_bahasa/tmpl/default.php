<?php
/**
 * Alih bahasa.
 *
 * Situs satu bahasa (plugin languagefilter mati) menampilkan bahasa Indonesia
 * sebagai aktif dan versi lain nonaktif — bukan tautan mati. Begitu operator
 * menyiapkan situs multibahasa (plugin + bahasa konten + menu asosiasi),
 * tautan antar bahasa otomatis memakai ?lang=<tag>.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\PluginHelper;

$catatan = trim((string) $params->get('catatan', ''));
$kode    = (int) $params->get('tampilkan_kode', 1) === 1;

$bahasa = LanguageHelper::getLanguages('lang_code');
$bahasa = is_array($bahasa) ? $bahasa : [];

// Urutkan: id-ID lebih dulu, lalu tag lain.
uksort($bahasa, static function ($a, $b): int {
    if ($a === $b) {
        return 0;
    }

    if ($a === 'id-ID') {
        return -1;
    }

    return $b === 'id-ID' ? 1 : strcmp((string) $a, (string) $b);
});

$multibahasa = PluginHelper::isEnabled('system', 'languagefilter');
$tagAktif    = $multibahasa
    ? Factory::getLanguage()->getTag()
    : (isset($bahasa['id-ID']) ? 'id-ID' : (string) array_key_first($bahasa));

$label = [
    'id-ID' => 'Bahasa Indonesia',
    'en-GB' => 'English',
    'en-US' => 'English',
    'ar-AA' => 'العربية',
];

$nama = static fn (string $tag, ?object $b = null): string => $label[$tag] ?? (string) ($b->title ?? $tag);

if ($bahasa === []) {
    $bahasa = ['id-ID' => (object) ['title' => 'Bahasa Indonesia']];
}
?>
<div class="pa-bahasa">
    <?php foreach ($bahasa as $tag => $b) : ?>
        <?php
        $tag = (string) $tag;
        $ini = ($tag === $tagAktif);
        ?>
        <?php if ($ini) : ?>
            <span class="pa-bahasa__item is-active" aria-current="true">
                <?php echo htmlspecialchars($nama($tag, $b), ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($kode) : ?><span class="pa-bahasa__kode"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
            </span>
        <?php elseif ($multibahasa) : ?>
            <a class="pa-bahasa__item" href="<?php echo htmlspecialchars((string) ($b->link ?? ('index.php?lang=' . $tag)), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($nama($tag, $b), ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($kode) : ?><span class="pa-bahasa__kode"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
            </a>
        <?php else : ?>
            <span class="pa-bahasa__item" aria-disabled="true">
                <?php echo htmlspecialchars($nama($tag, $b), ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($kode) : ?><span class="pa-bahasa__kode"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$multibahasa && $catatan !== '') : ?>
        <p class="pa-muted" style="margin:0;font-size:.74rem"><?php echo htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
</div>
