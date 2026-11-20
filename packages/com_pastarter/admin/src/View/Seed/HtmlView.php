<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\View\Seed;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

defined('_JEXEC') or die;

final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        \Joomla\CMS\Toolbar\ToolbarHelper::title('PA Starter — Pasang Konten Awal', 'checkin');
    }
}
