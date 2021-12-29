<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DateTimeZone;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Job\RunJobCommands;
use PHPMate\Domain\Job\RunJobRecipe;
use PHPMate\Domain\Job\UpdateMergeRequest;
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
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Infrastructure\Cookbook\StaticRecipesCollection;
use PHPMate\Infrastructure\FileSystem\TemporaryLocalFileSystemApplicationDirectoryProvider;
use PHPMate\Infrastructure\Git\PHPMateBranchNameProvider;
use PHPMate\Infrastructure\GitLab\GitLab;
use PHPMate\Infrastructure\Job\LoggingSymfonyProcessRunJobCommands;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
use PHPMate\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessComposerBinary;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessGitBinary;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessRectorBinary;
use PHPMate\UseCase\ExecuteJobHandler;

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
        ->public();

    // Controllers
    $services->load('PHPMate\\Ui\\Controller\\', __DIR__ . '/../../../Ui/Controller/{*Controller.php}');

    // Use cases
    $services->load('PHPMate\\UseCase\\', __DIR__ . '/../../../UseCase/{*Handler.php}');

    // Console commands
    $services->load('PHPMate\\Cli\\', __DIR__ . '/../../../Cli/**/{*ConsoleCommand.php}');

    // Read model providers
    $services->load('PHPMate\\Ui\\ReadModel\\', __DIR__ . '/../../../Ui/ReadModel/**/{Provide*.php}');

    // Infrastructure - load all implementations
    $services->load('PHPMate\\Infrastructure\\', __DIR__ . '/../../../Infrastructure/**/{*.php}')
        ->exclude([
            __DIR__ . '/../../../Infrastructure/Persistence/Doctrine/{Type,Migrations}', // Doctrine migrations and custom types
            __DIR__ . '/../../../Infrastructure/Symfony', // Symfony framework configuration
        ]);

    // Tools
    $services->load('PHPMate\\Domain\\Tools\\', __DIR__ . '/../../../Domain/Tools/**/{*.php}')
        ->exclude([
            __DIR__ . '/../../../Domain/Tools/**/{Exception,Value}/*'
        ]);

    $services->set(TemporaryLocalFileSystemApplicationDirectoryProvider::class)
        ->args([
            param(ConfigParameters::WORKING_DIRECTORY_BASE_DIR)
        ]);

    $services->alias(ApplicationDirectoryProvider::class, TemporaryLocalFileSystemApplicationDirectoryProvider::class);

    $services->set(Git::class);
    $services->set(Composer::class);
    $services->set(ProcessLogger::class);

    $services->alias(ComposerBinary::class, SymfonyProcessComposerBinary::class);

    $services->alias(GitBinary::class, SymfonyProcessGitBinary::class);

    $services->alias(BranchNameProvider::class, PHPMateBranchNameProvider::class);

    $services->alias(RectorBinary::class, SymfonyProcessRectorBinary::class);

    $services->set(DateTimeZone::class, DateTimeZone::class)->args(['UTC']);
    $services->set(SystemClock::class);
    $services->alias(Clock::class, SystemClock::class);

    $services->set(BuildApplication::class);
    $services->set(PrepareApplicationGitRepository::class);

    $services->alias(GitProvider::class, GitLab::class);
    $services->alias(CheckWriteAccessToRemoteRepository::class, GitLab::class);
    $services->alias(GetLastCommitOfDefaultBranch::class, GitLab::class);

    $services->alias(JobsCollection::class, DoctrineJobsCollection::class);

    $services->alias(TasksCollection::class, DoctrineTasksCollection::class);

    $services->alias(ProjectsCollection::class, DoctrineProjectsCollection::class);

    $services->alias(RecipesCollection::class, StaticRecipesCollection::class);

    $services->alias(RunJobCommands::class, LoggingSymfonyProcessRunJobCommands::class);
    $services->set(RunJobRecipe::class); // TODO: think how to do it automatically, it is not interface
    $services->set(UpdateMergeRequest::class); // TODO: think how to do it automatically, it is not interface
};
