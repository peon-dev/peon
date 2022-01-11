<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DateTimeZone;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use Peon\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\RunJobCommands;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\PrepareApplicationGitRepository;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Composer\ComposerBinary;
use Peon\Domain\PhpApplication\ApplicationDirectoryProvider;
use Peon\Domain\Tools\Git\BranchNameProvider;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\Tools\Git\GitBinary;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Tools\Rector\RectorBinary;
use Peon\Infrastructure\Cookbook\StaticRecipesCollection;
use Peon\Infrastructure\FileSystem\TemporaryLocalFileSystemApplicationDirectoryProvider;
use Peon\Infrastructure\Git\PeonBranchNameProvider;
use Peon\Infrastructure\GitLab\GitLab;
use Peon\Infrastructure\Job\LoggingSymfonyProcessRunJobCommands;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
use Peon\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessComposerBinary;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessGitBinary;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessRectorBinary;
use Peon\UseCase\ExecuteJobHandler;

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
    $services->load('Peon\\Ui\\Controller\\', __DIR__ . '/../../../Ui/Controller/{*Controller.php}');

    // Event subscribers
    $services->load('Peon\\Subscribers\\', __DIR__ . '/../../../Subscribers/{*When*.php}');

    // Use cases
    $services->load('Peon\\UseCase\\', __DIR__ . '/../../../UseCase/{*Handler.php}');

    // Console commands
    $services->load('Peon\\Cli\\', __DIR__ . '/../../../Cli/**/{*ConsoleCommand.php}');

    // Read model providers
    $services->load('Peon\\Ui\\ReadModel\\', __DIR__ . '/../../../Ui/ReadModel/**/{Provide*.php}');

    // Infrastructure - load all implementations
    $services->load('Peon\\Infrastructure\\', __DIR__ . '/../../../Infrastructure/**/{*.php}')
        ->exclude([
            __DIR__ . '/../../../Infrastructure/Persistence/Doctrine/{Type,Migrations}', // Doctrine migrations and custom types
            __DIR__ . '/../../../Infrastructure/Symfony', // Symfony framework configuration
        ]);

    // Tools
    $services->load('Peon\\Domain\\Tools\\', __DIR__ . '/../../../Domain/Tools/**/{*.php}')
        ->exclude([
            __DIR__ . '/../../../Domain/Tools/**/{Exception,Value,Event}/*'
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

    $services->alias(BranchNameProvider::class, PeonBranchNameProvider::class);

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
