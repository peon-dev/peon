<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Symfony\DependencyInjection;

use PHPMate\Worker\Domain\Notification\Notifier;
use PHPMate\Worker\Infrastructure\Notifier\SymfonyNotifier;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Notifier\NotifierInterface;

class ContainerFactory
{
    public static ?NotifierInterface $symfonyNotifier = null;

    /**
     * @param array<string> $customConfigs
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
            $containerBuilder->register(Notifier::class, SymfonyNotifier::class)
                ->setArguments([self::$symfonyNotifier]);
        }

        foreach ($customConfigs as $customConfig) {
            $loader->load($customConfig);
        }

        $containerBuilder->compile();

        return $containerBuilder;
    }
}
