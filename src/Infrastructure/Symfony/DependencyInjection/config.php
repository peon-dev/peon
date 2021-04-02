<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPMate\Infrastructure\Composer\ShellExecComposerBinary;
use PHPMate\Infrastructure\Git\ShellExecGitBinary;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;

return static function(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public(); // Allow access services via container in tests


    $services->set(Composer::class);
    $services->set(ComposerBinary::class, ShellExecComposerBinary::class);

    $services->set(Git::class);
    $services->set(GitBinary::class, ShellExecGitBinary::class);

    $services->set(RunRectorOnGitlabRepositoryUseCase::class);
};
