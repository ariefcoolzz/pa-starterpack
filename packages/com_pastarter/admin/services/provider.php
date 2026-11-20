<?php
declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use PA\Starter\Administrator\Extension\PastarterComponent;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\PA\\Starter\\Administrator'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\PA\\Starter\\Administrator'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new PastarterComponent(
                    $container->get(MVCFactoryInterface::class),
                    $container
                );
                $component->setDispatcher(
                    $container->get(\Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface::class)
                        ->createDispatcher($component)
                );

                return $component;
            }
        );
    }
};
