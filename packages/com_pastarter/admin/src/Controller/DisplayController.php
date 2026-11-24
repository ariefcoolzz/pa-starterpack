<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

final class DisplayController extends BaseController
{
    /** @var string */
    protected $default_view = 'seed';
}
