<?php
/**
 * Navigasi utama: markup .pa-menu/.pa-submenu sesuai CSS template.
 * Hanya anak langsung akar yang menjadi menu tingkat 1 (6 menu SK);
 * submenu diambil dari getChildren() agar tidak menjadi daftar datar.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app      = Factory::getApplication();
$menu     = $app->getMenu();
$activeId = (int) ($menu->getActive()->id ?? 0);
$menutype = $params->get('menutype', 'mainmenu') ?: 'mainmenu';

$semua = $menu->getItems('menutype', $menutype) ?: [];
$akar  = array_values(array_filter($semua, static fn ($i): bool => (int) $i->parent_id === 1));

$render = static function (array $items, int $level) use (&$render, $activeId): string {
    $kelas = $level > 1 ? 'pa-submenu' : 'pa-menu';
    $html  = '<ul class="' . $kelas . '">';

    foreach ($items as $item) {
        $aktif = ((int) $item->id === $activeId) ? ' is-active' : '';
        $html .= '<li' . ($aktif ? ' class="' . trim($aktif) . '"' : '') . '>';
        $html .= '<a href="' . $item->flink . '"' . ($aktif ? ' aria-current="page"' : '') . '>'
            . $item->title . '</a>';

        if ($level === 1 && $item->getChildren()) {
            $html .= $render($item->getChildren(), $level + 1);
        }

        $html .= '</li>';
    }

    return $html . '</ul>';
};

echo $render($akar, 1);
