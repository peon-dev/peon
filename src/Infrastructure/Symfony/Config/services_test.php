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
        ->autowire();

    $services->set(StatefulRandomPostfixBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, StatefulRandomPostfixBranchNameProvider::class);

    $services->set(JobsCollection::class, InMemoryJobsCollection::class);

    $services->set(TasksCollection::class, InMemoryTasksCollection::class);

    $services->set(ProjectsCollection::class, InMemoryProjectsCollection::class);
};
