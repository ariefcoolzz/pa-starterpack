<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use PA\Starter\Administrator\Seeder;

defined('_JEXEC') or die;

final class SeedController extends BaseController
{
    public function run(): void
    {
        $this->checkToken('post');

        try {
            $seeder = new Seeder(Factory::getContainer());
            $log    = $seeder->run(['seed']);

            Factory::getApplication()->enqueueMessage(
                sprintf(
                    'Konten awal selesai: dibuat %d, dilewati %d, gagal %d.',
                    $log['dibuat'],
                    $log['dilewati'],
                    $log['gagal']
                ),
                'success'
            );
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage('Gagal: ' . $e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_pastarter&view=seed', false));
    }
}
