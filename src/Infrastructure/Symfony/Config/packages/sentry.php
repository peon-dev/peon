<?php declare(strict_types=1);

use Sentry\Integration\IgnoreErrorsIntegration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\SentryConfig;

return static function (SentryConfig $sentryConfig, ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(IgnoreErrorsIntegration::class)
        ->args([
            [
                'ignore_exceptions' => [
                    Symfony\Component\Security\Core\Exception\AccessDeniedException::class,
                ],
            ],
        ]);

    $sentryConfig->tracing()
        ->enabled(false);

    $sentryConfig->registerErrorListener(false);

    $sentryConfig->dsn('%env(SENTRY_DSN)%');

    $sentryConfig->messenger()
        ->enabled(true)
        ->captureSoftFails(true);

    $sentryConfig->options()
        ->environment('%kernel.environment%')
        ->sendDefaultPii(true)
        ->integrations([
            IgnoreErrorsIntegration::class,
        ]);
};
