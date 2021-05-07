<?php

declare(strict_types=1);

namespace PHPMate\Dashboard;

use Nette\Bootstrap\Configurator;
use Symfony\Component\Dotenv\Dotenv;


class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator;

        (new Dotenv())
            ->usePutenv()
            ->loadEnv(__DIR__ . '/../.env');

        if (isset($_ENV['APP_DEBUG']) && (bool) $_ENV['APP_DEBUG'] === true) {
            $configurator->setDebugMode(true);
        }

        $configurator->enableTracy(__DIR__ . '/../var/log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../var/cache');

        $configurator->addConfig(__DIR__ . '/../config/common.neon');

        return $configurator;
    }
}
