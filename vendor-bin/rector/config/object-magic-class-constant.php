<?php

use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ClassOnObjectRector::class);
};
