<?php

declare(strict_types=1);

use Lcobucci\Clock\Clock;
use Lcobucci\Clock\FrozenClock;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Infrastructure\Git\StatefulRandomPostfixProvideBranchName;
use Peon\Infrastructure\GitProvider\DummyGitProvider;
use Peon\Infrastructure\Mercure\DummyHub;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
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

    $services->set(HubInterface::class, DummyHub::class);

    $services->alias(Clock::class, FrozenClock::class);

    $services->set(GitProvider::class, DummyGitProvider::class);
};
