<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import(__DIR__ . '/../../../Ui/Controller/', 'annotation');

    $routingConfigurator->add('logout', '/logout')
        ->methods(['GET']);
};
