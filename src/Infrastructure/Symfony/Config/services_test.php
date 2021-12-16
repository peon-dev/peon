<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Infrastructure\Git\StatefulRandomPostfixBranchNameProvider;
use PHPMate\Infrastructure\GitProvider\DummyCheckWriteAccessToRemoteRepository;
use PHPMate\Infrastructure\GitProvider\DummyGetLastCommitOfDefaultBranch;

return static function(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Data fixtures
    $services->load('PHPMate\\Tests\\DataFixtures\\', __DIR__ . '/../../../../tests/DataFixtures/{*.php}');

    $services->alias(BranchNameProvider::class, StatefulRandomPostfixBranchNameProvider::class);

    $services->alias(CheckWriteAccessToRemoteRepository::class, DummyCheckWriteAccessToRemoteRepository::class);
    $services->alias(GetLastCommitOfDefaultBranch::class, DummyGetLastCommitOfDefaultBranch::class);
};
