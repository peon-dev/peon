<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DateTimeZone;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use PHPMate\Worker\App\RunRectorOnGitlabRepositoryLauncher;
use PHPMate\Worker\Domain\Composer\Composer;
use PHPMate\Worker\Domain\Composer\ComposerBinary;
use PHPMate\Worker\Domain\FileSystem\ProjectDirectoryProvider;
use PHPMate\Worker\Domain\Git\BranchNameProvider;
use PHPMate\Worker\Domain\Git\Git;
use PHPMate\Worker\Domain\Git\GitBinary;
use PHPMate\Worker\Domain\Gitlab\Gitlab;
use PHPMate\Worker\Domain\Job\JobRepository;
use PHPMate\Worker\Domain\Process\ProcessLogger;
use PHPMate\Worker\Domain\Notification\Notifier;
use PHPMate\Worker\Domain\Rector\Rector;
use PHPMate\Worker\Domain\Rector\RectorBinary;
use PHPMate\Worker\Infrastructure\Dummy\DummyNotifier;
use PHPMate\Worker\Infrastructure\FileSystem\TemporaryLocalFileSystemProjectDirectoryProvider;
use PHPMate\Worker\Infrastructure\Git\PHPMateBranchNameProvider;
use PHPMate\Worker\Infrastructure\Gitlab\HttpGitlab;
use PHPMate\Worker\Infrastructure\Job\FileSystem\FileSystemJobRepository;
use PHPMate\Worker\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use PHPMate\Worker\Infrastructure\Symfony\Process\SymfonyProcessComposerBinary;
use PHPMate\Worker\Infrastructure\Symfony\Process\SymfonyProcessGitBinary;
use PHPMate\Worker\Infrastructure\Symfony\Process\SymfonyProcessRectorBinary;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepositoryUseCase;

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

    $services->set(FileSystemJobRepository::class)->args([
         __DIR__ . '/../../../../data',
    ]);
    $services->alias(JobRepository::class, FileSystemJobRepository::class);

    $services->set(RunRectorOnGitlabRepositoryLauncher::class);

    $services->set(DateTimeZone::class, DateTimeZone::class)->args(['UTC']);
    $services->set(SystemClock::class);
    $services->alias(Clock::class, SystemClock::class);
};
