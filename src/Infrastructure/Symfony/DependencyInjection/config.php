<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PHPMate\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\FileSystem\ProjectDirectoryProvider;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Job\JobRepository;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Notification\Notifier;
use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Infrastructure\Dummy\DummyNotifier;
use PHPMate\Infrastructure\FileSystem\TemporaryLocalFileSystemProjectDirectoryProvider;
use PHPMate\Infrastructure\Git\PHPMateBranchNameProvider;
use PHPMate\Infrastructure\Gitlab\HttpGitlab;
use PHPMate\Infrastructure\Job\FileSystem\FileSystemJobRepository;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use PHPMate\Infrastructure\Symfony\Process\SymfonyProcessComposerBinary;
use PHPMate\Infrastructure\Symfony\Process\SymfonyProcessGitBinary;
use PHPMate\Infrastructure\Symfony\Process\SymfonyProcessRectorBinary;
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


    $services->set(TemporaryLocalFileSystemProjectDirectoryProvider::class)
        ->args([
            param(ConfigParameters::WORKING_DIRECTORY_BASE_DIR)
        ]);
    $services->alias(ProjectDirectoryProvider::class, TemporaryLocalFileSystemProjectDirectoryProvider::class);

    $services->set(Composer::class);
    $services->set(ComposerBinary::class, SymfonyProcessComposerBinary::class);

    $services->set(Git::class);
    $services->set(GitBinary::class, SymfonyProcessGitBinary::class);

    $services->set(PHPMateBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, PHPMateBranchNameProvider::class);

    $services->set(HttpGitlab::class);
    $services->alias(Gitlab::class, HttpGitlab::class);

    $services->set(Rector::class);
    $services->set(RectorBinary::class, SymfonyProcessRectorBinary::class);

    $services->set(RunRectorOnGitlabRepositoryUseCase::class);

    $services->set(DummyNotifier::class);
    $services->alias(Notifier::class, DummyNotifier::class);

    $services->set(ProcessLogger::class);

    $services->set(FileSystemJobRepository::class);
    $services->alias(JobRepository::class, FileSystemJobRepository::class);

    $services->set(RunRectorOnGitlabRepositoryLauncher::class);
};
