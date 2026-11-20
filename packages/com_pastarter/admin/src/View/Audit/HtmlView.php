<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\View\Audit;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use PA\Starter\Administrator\Auditor;

defined('_JEXEC') or die;

final class HtmlView extends BaseHtmlView
{
    /** @var array<string, mixed> */
    public array $report = [];

    public function display($tpl = null): void
    {
        $base = Uri::root();
        $this->report = (new Auditor(Factory::getContainer()))->run($base);

        ToolbarHelper::title('PA Starter — Kesiapan SINOVA', 'checkin');

        parent::display($tpl);
    }
}
