<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Infrastructure\Git\StatefulRandomPostfixBranchNameProvider;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;

return static function(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public(); // Allow access services via container in tests


    $services->set(StatefulRandomPostfixBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, StatefulRandomPostfixBranchNameProvider::class);

    $services->set(InMemoryJobsCollection::class);
    $services->alias(JobsCollection::class, InMemoryJobsCollection::class);

    $services->set(InMemoryTasksCollection::class);
    $services->alias(TasksCollection::class, InMemoryTasksCollection::class);

    $services->set(InMemoryProjectsCollection::class);
    $services->alias(ProjectsCollection::class, InMemoryProjectsCollection::class);
};
