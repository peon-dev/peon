<?php

declare(strict_types=1);

use Mangoweb\MonologTracyHandler\TracyHandler;
use Mangoweb\MonologTracyHandler\TracyProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(TracyProcessor::class);

    $services->set(TracyHandler::class)
        ->args(['$localBlueScreenDirectory' => '%kernel.logs_dir%']);
};
