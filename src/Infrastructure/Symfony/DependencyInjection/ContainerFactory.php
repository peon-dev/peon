<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ContainerFactory
{
    public static function createContainer(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('config.php');

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
