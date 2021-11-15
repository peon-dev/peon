<?php

namespace PHPMate\Infrastructure\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class PHPMateKernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import(__DIR__ . '/Config/packages/*.php');
        $container->import(__DIR__ . '/Config/packages/'.$this->environment.'/*.php');

        $container->import(__DIR__ . '/Config/services.php');
        $container->import(__DIR__ . '/Config/{services}_'.$this->environment.'.php');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/Config/{routes}/'.$this->environment.'/*.php');
        $routes->import(__DIR__ . '/Config/{routes}/*.php');

        $routes->import(__DIR__ . '/Config/routes.php');
    }


    public function getProjectDir(): string
    {
        return __DIR__ . '/../../..';
    }


    /**
     * @return iterable<Bundle>
     */
    public function registerBundles(): iterable
    {
        /** @var array<class-string<Bundle>, array<string>> $contents */
        $contents = require __DIR__ . '/Config/bundles.php';

        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }
}
