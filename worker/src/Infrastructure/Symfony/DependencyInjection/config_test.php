<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Worker\Domain\Git\BranchNameProvider;
use PHPMate\Worker\Domain\Job\JobRepository;
use PHPMate\Worker\Infrastructure\Git\StatefulRandomPostfixBranchNameProvider;
use PHPMate\Worker\Infrastructure\Memory\InMemoryJobRepository;

return static function(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public(); // Allow access services via container in tests


    $services->set(StatefulRandomPostfixBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, StatefulRandomPostfixBranchNameProvider::class);

    $services->set(InMemoryJobRepository::class);
    $services->alias(JobRepository::class, InMemoryJobRepository::class);
};
