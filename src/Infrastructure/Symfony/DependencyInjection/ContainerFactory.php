<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ContainerFactory
{
    public static function create(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('config.php');

        $envSpecificConfig = 'config_' . $_ENV['APP_ENV'] . '.php';
        if (is_file(__DIR__ . '/' . $envSpecificConfig)) {
            $loader->load($envSpecificConfig);
        }

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
