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
use Peon\Domain\Job\GetPathsToProcess;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\PrepareApplicationGitRepository;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Process\ProcessesCollection;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\SanitizeProcessCommand;
use Peon\Domain\Process\SanitizeProcessOutput;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\PhpApplication\ProvideApplicationDirectory;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Domain\Tools\Git\Git;
use Peon\Infrastructure\Cookbook\StaticRecipesCollection;
use Peon\Infrastructure\FileSystem\TemporaryLocalFileSystemProvideApplicationDirectory;
use Peon\Infrastructure\Git\PeonProvideBranchName;
use Peon\Infrastructure\GitLab\GitLab;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProcessesCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessRunProcess;
use Peon\Infrastructure\Scheduler\DoctrineGetRecipeSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetTaskSchedules;
use Peon\Infrastructure\Symfony\DependencyInjection\ConfigParameters;

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
    $services->load('Peon\\Ui\\ReadModel\\', __DIR__ . '/../../../Ui/ReadModel/**/{Provide*,Count*}.php');

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

    $services->set(TemporaryLocalFileSystemProvideApplicationDirectory::class)
        ->args([
            param(ConfigParameters::WORKING_DIRECTORY_BASE_DIR)
        ]);

    $services->alias(ProvideApplicationDirectory::class, TemporaryLocalFileSystemProvideApplicationDirectory::class);

    $services->set(Git::class);
    $services->set(Composer::class);

    $services->alias(ProvideBranchName::class, PeonProvideBranchName::class);


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

    $services->alias(ProcessesCollection::class, DoctrineProcessesCollection::class);

    $services->set(ExecuteCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(ExecuteCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(RunJobRecipe::class); // TODO: think how to do it automatically, it is not interface
    $services->set(UpdateMergeRequest::class); // TODO: think how to do it automatically, it is not interface
    $services->set(SanitizeProcessCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(SanitizeProcessOutput::class); // TODO: think how to do it automatically, it is not interface
    $services->set(GetPathsToProcess::class); // TODO: think how to do it automatically, it is not interface

    $services->set(ShouldSchedule::class); // TODO: think how to do it automatically, it is not interface
    $services->set(GetRecipeSchedules::class, DoctrineGetRecipeSchedules::class);
    $services->set(GetTaskSchedules::class, DoctrineGetTaskSchedules::class);
    $services->set(RunProcess::class, SymfonyProcessRunProcess::class);
};
