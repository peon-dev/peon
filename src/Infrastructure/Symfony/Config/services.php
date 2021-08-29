<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DateTimeZone;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Composer\ComposerBinary;
use PHPMate\Domain\PhpApplication\ApplicationDirectoryProvider;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\Tools\Git\GitBinary;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Notification\Notifier;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Infrastructure\Notification\DummyNotifier;
use PHPMate\Infrastructure\FileSystem\TemporaryLocalFileSystemApplicationDirectoryProvider;
use PHPMate\Infrastructure\Git\PHPMateBranchNameProvider;
use PHPMate\Infrastructure\GitLab\GitLab;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessComposerBinary;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessGitBinary;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessRectorBinary;

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
        ->autowire();

    // Controllers
    $services->load('PHPMate\\Ui\\Controller\\', __DIR__ . '/../../../Ui/Controller/{*Controller.php}');


    $services->set(TemporaryLocalFileSystemApplicationDirectoryProvider::class)
        ->args([
            param(ConfigParameters::WORKING_DIRECTORY_BASE_DIR)
        ]);
    $services->alias(ApplicationDirectoryProvider::class, TemporaryLocalFileSystemApplicationDirectoryProvider::class);

    $services->set(Composer::class);
    $services->set(ComposerBinary::class, SymfonyProcessComposerBinary::class);

    $services->set(Git::class);
    $services->set(GitBinary::class, SymfonyProcessGitBinary::class);

    $services->set(PHPMateBranchNameProvider::class);
    $services->alias(BranchNameProvider::class, PHPMateBranchNameProvider::class);

    $services->set(Rector::class);
    $services->set(RectorBinary::class, SymfonyProcessRectorBinary::class);

    $services->set(DummyNotifier::class);
    $services->alias(Notifier::class, DummyNotifier::class);

    $services->set(ProcessLogger::class);

    $services->set(DateTimeZone::class, DateTimeZone::class)->args(['UTC']);
    $services->set(SystemClock::class);
    $services->alias(Clock::class, SystemClock::class);

    // Use cases
    $services->load('PHPMate\UseCase\\', __DIR__ . '/../../../UseCase/*UseCase.php');

    $services->set(BuildApplication::class);
    $services->set(PrepareApplicationGitRepository::class);

    $services->set(GitLab::class);
    $services->alias(GitProvider::class, GitLab::class);

    $services->set(JobsCollection::class, InMemoryJobsCollection::class);

    $services->set(TasksCollection::class, InMemoryTasksCollection::class);

    $services->set(ProjectsCollection::class, InMemoryProjectsCollection::class);
};
