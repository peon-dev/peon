<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sensio_framework_extra', [
        'router' => [
            'annotations' => false,
        ],
        'request' => [
            'converters' => true,
            'auto_convert' => false,
        ],
    ]);
};
