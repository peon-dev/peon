<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\FileSystem\WorkingDirectoryProvider;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Infrastructure\Composer\ShellExecComposerBinary;
use PHPMate\Infrastructure\FileSystem\TemporaryLocalFileSystemWorkingDirectoryProvider;
use PHPMate\Infrastructure\Git\PHPMateBranchNameProvider;
use PHPMate\Infrastructure\Git\ShellExecGitBinary;
use PHPMate\Infrastructure\Gitlab\HttpGitlabClient;
use PHPMate\Infrastructure\Rector\ShellExecRectorBinary;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;

return static function(ContainerConfigurator $configurator): void
{
    $parameters = $configurator->parameters();

    $parameters->set(
      ConfigParameters::WORKING_DIRECTORY_BASE_DIR,
      __DIR__ . '/../../../../var/working_directories',
    );

    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public(); // Allow access services via container in tests


    $services->set(TemporaryLocalFileSystemWorkingDirectoryProvider::class)
        ->args([
            param(ConfigParameters::WORKING_DIRECTORY_BASE_DIR)
        ]);
    $services->alias(WorkingDirectoryProvider::class, TemporaryLocalFileSystemWorkingDirectoryProvider::class);

    $services->set(Composer::class);
    $services->set(ComposerBinary::class, ShellExecComposerBinary::class);

    $services->set(Git::class);
    $services->set(GitBinary::class, ShellExecGitBinary::class);

    $services->set(PHPMateBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, PHPMateBranchNameProvider::class);

    $services->set(Gitlab::class, HttpGitlabClient::class);

    $services->set(Rector::class);
    $services->set(RectorBinary::class, ShellExecRectorBinary::class);

    $services->set(RunRectorOnGitlabRepositoryUseCase::class);

};
