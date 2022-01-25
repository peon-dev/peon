<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Peon\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use Peon\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Infrastructure\Git\StatefulRandomPostfixProvideBranchName;
use Peon\Infrastructure\GitProvider\DummyCheckWriteAccessToRemoteRepository;
use Peon\Infrastructure\GitProvider\DummyGetLastCommitOfDefaultBranch;
use Peon\Infrastructure\Mercure\DummyHub;
use Symfony\Component\Mercure\HubInterface;

return static function(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Data fixtures
    $services->load('Peon\\Tests\\DataFixtures\\', __DIR__ . '/../../../../tests/DataFixtures/{*.php}');

    $services->alias(ProvideBranchName::class, StatefulRandomPostfixProvideBranchName::class);

    $services->alias(CheckWriteAccessToRemoteRepository::class, DummyCheckWriteAccessToRemoteRepository::class);
    $services->alias(GetLastCommitOfDefaultBranch::class, DummyGetLastCommitOfDefaultBranch::class);

    $services->set(HubInterface::class, DummyHub::class);
};
