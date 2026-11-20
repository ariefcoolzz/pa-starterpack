<?php
declare(strict_types=1);

namespace PA\Starter\Administrator\Extension;

use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Psr\Container\ContainerInterface;

final class PastarterComponent extends MVCComponent
{
    public function __construct(
        private readonly MVCFactoryInterface $mvcFactory,
        private readonly ContainerInterface $container
    ) {
    }

    public function getMVCFactory(): MVCFactoryInterface
    {
        return $this->mvcFactory;
    }
}
