<?php

namespace PHPMate\Infrastructure\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class PHPMateKernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import(__DIR__ . '/config/{packages}/*.yaml');
        $container->import(__DIR__ . '/config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import(__DIR__ . '/config/services.yaml');
            $container->import(__DIR__ . '/config/{services}_'.$this->environment.'.yaml');
        } else {
            $container->import(__DIR__ . '/config/{services}.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import(__DIR__ . '/config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import(__DIR__ . '/config/routes.yaml');
        } else {
            $routes->import(__DIR__ . '/config/{routes}.php');
        }
    }
}
