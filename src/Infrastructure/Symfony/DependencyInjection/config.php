<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Infrastructure\Composer\ShellExecComposerBinary;
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

    $services->set(RunRectorOnGitlabRepositoryUseCase::class);
};
