<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\DependencyInjection;

use PHPMate\Domain\Notification\Notifier;
use PHPMate\Infrastructure\Notifier\SymfonyNotifier;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Notifier\NotifierInterface;

class ContainerFactory
{
    public static ?NotifierInterface $symfonyNotifier = null;

    /**
     * @param array<string>
     */
    public static function create(array $customConfigs = []): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('config.php');

        $envSpecificConfig = 'config_' . $_ENV['APP_ENV'] . '.php';
        if (is_file(__DIR__ . '/' . $envSpecificConfig)) {
            $loader->load($envSpecificConfig);
        }

        if (self::$symfonyNotifier !== null) {
            $containerBuilder->set(SymfonyNotifier::class, new SymfonyNotifier(self::$symfonyNotifier));
            $containerBuilder->setAlias(Notifier::class, SymfonyNotifier::class);
        }

        foreach ($customConfigs as $customConfig) {
            $loader->load($customConfig);
        }

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
