#!/usr/bin/env php
<?php
declare(strict_types=1);

use Joomla\Console\Command\AbstractCommand;
use PA\Starter\Administrator\Auditor;
use PA\Starter\Administrator\Seeder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Utilitas CLI untuk PA Starterpack (Joomla 6.2).
 *
 * Pemakaian (di root situs Joomla sebagai cli/pa.php, atau dari repo via JOOMLA_ROOT):
 *   PA_MANIFEST=/path/ke/manifest php cli/pa.php seed
 *   PA_MANIFEST=/path/ke/manifest php cli/pa.php audit http://pa-contoh.test
 */

const _JEXEC = 1;
const JOOMLA_MINIMUM_PHP = '8.3.0';

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<')) {
    fwrite(STDERR, 'Membutuhkan PHP >= ' . JOOMLA_MINIMUM_PHP . ', terpasang: ' . PHP_VERSION . PHP_EOL);
    exit(1);
}

$joomlaRoot = getenv('JOOMLA_ROOT') ?: dirname(__DIR__);
$joomlaRoot = rtrim((string) $joomlaRoot, '/\\');

if (file_exists($joomlaRoot . '/defines.php')) {
    require_once $joomlaRoot . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', $joomlaRoot);
    require_once JPATH_BASE . '/includes/defines.php';
}

if (
    !file_exists(JPATH_CONFIGURATION . '/configuration.php')
    || (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
) {
    fwrite(STDERR, 'Joomla belum terpasang (configuration.php tidak ditemukan di ' . JPATH_CONFIGURATION . ').' . PHP_EOL);
    exit(1);
}

require_once JPATH_BASE . '/includes/framework.php';

$container = \Joomla\CMS\Factory::getContainer();

$container->alias('session', 'session.cli')
    ->alias('JSession', 'session.cli')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.cli');

/** @var \Joomla\CMS\Application\ConsoleApplication $app */
$app = $container->get(\Joomla\Console\Application::class);
\Joomla\CMS\Factory::$application = $app;

$seedCommand = new class ($container) extends AbstractCommand {
    protected static $defaultName = 'seed';

    public function __construct(private readonly ContainerInterface $di)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Menyemai kategori, artikel, 35 halaman indikator, menu, dan modul PA Starter.');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $log = (new Seeder($this->di))->run($_SERVER['argv'] ?? ['seed']);

        $dibuat     = (int) ($log['dibuat'] ?? 0);
        $dilewati   = (int) ($log['dilewati'] ?? 0);
        $diperbarui = (int) ($log['diperbarui'] ?? 0);
        $gagal      = (int) ($log['gagal'] ?? 0);

        $output->writeln(sprintf(
            'Seed selesai: dibuat %d, dilewati %d, diperbarui %d, gagal %d',
            $dibuat,
            $dilewati,
            $diperbarui,
            $gagal
        ));
        $output->writeln(json_encode($log, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $gagal > 0 ? Command::FAILURE : Command::SUCCESS;
    }
};

$auditCommand = new class ($container) extends AbstractCommand {
    protected static $defaultName = 'audit';

    public function __construct(private readonly ContainerInterface $di)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Memeriksa 35 URL indikator SINOVA serta estimasi skor.');
        $this->addArgument('base', InputArgument::REQUIRED, 'URL dasar situs, mis. http://pa-satker-sandbox.test');
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $base   = (string) $input->getArgument('base');
        $report = (new Auditor($this->di))->run($base);

        $rows   = $report['indicators'] ?? [];
        $ok200  = 0;

        foreach ($rows as $idx => $row) {
            $status = (int) ($row['status'] ?? 0);
            if ($status === 200) {
                $ok200++;
            }

            $output->writeln(sprintf(
                '%2d. [%d] kosong=%2d skor=%2d  %-13s  %s',
                $idx + 1,
                $status,
                (int) ($row['kosong'] ?? 0),
                (int) ($row['score'] ?? 0),
                (string) ($row['id'] ?? ''),
                (string) ($row['url'] ?? '')
            ));
        }

        $est = $report['estimate'] ?? [];
        $output->writeln('---');
        $output->writeln(sprintf(
            'Ringkasan: HTTP 200 = %d/%d | Siap (0 placeholder) = %d/%d | Konten=%.1f Desain=%.1f Infra=%.1f Total=%.1f',
            $ok200,
            count($rows),
            (int) ($report['ready'] ?? 0),
            count($rows),
            (float) ($est['content'] ?? 0),
            (float) ($est['design'] ?? 0),
            (float) ($est['infra'] ?? 0),
            (float) ($est['total'] ?? 0)
        ));

        return $ok200 === count($rows) ? Command::SUCCESS : Command::FAILURE;
    }
};

$app->addCommand($seedCommand);
$app->addCommand($auditCommand);
$app->execute();
