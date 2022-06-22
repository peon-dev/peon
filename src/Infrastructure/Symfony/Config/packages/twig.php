<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $composerPackage = InstalledVersions::getRootPackage();

    $containerConfigurator->extension('twig', [
        'default_path' => '%kernel.project_dir%/src/Ui/templates',
        'form_themes' => ['bootstrap_5_layout.html.twig'],
        'date' => [
            'timezone' => 'Europe/Prague',
        ],
        'globals' => [
            'peon_app_version' => $composerPackage['pretty_version'],
        ],
    ]);
};
