<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Job\JobRepository;
use PHPMate\Infrastructure\Git\StatefulRandomPostfixBranchNameProvider;
use PHPMate\Infrastructure\Memory\InMemoryJobRepository;

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
