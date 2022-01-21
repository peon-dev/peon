<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->extension('sentry', [
        'dsn' => '%env(SENTRY_DSN)%',
        'messenger' => [
            'enabled' => true,
            'capture_soft_fails' => true,
        ],
        'options' => [
            'environment' => '%kernel.environment%',
            // 'release' => '%env(VERSION)%', // TODO
            'send_default_pii' => true,
        ],
    ]);
};
