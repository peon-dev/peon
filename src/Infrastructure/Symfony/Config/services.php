<?php

declare(strict_types=1);

use Lcobucci\Clock\Clock;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\Clock\SystemClock;
use Monolog\Processor\PsrLogMessageProcessor;
use Peon\Domain\Application\DetectApplicationLanguage;
use Peon\Domain\Container\DetectContainerImage;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\Job\GetLongRunningJobs;
use Peon\Domain\Job\GetPathsToProcess;
use Peon\Domain\Job\GetRecipeCommands;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\PhpApplication\BuildPhpApplication;
use Peon\Domain\Application\PrepareApplicationGitRepository;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Process\ProcessesCollection;
use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\SanitizeProcessCommand;
use Peon\Domain\Process\SanitizeProcessOutput;
use Peon\Domain\Project\GetProjectIdentifiers;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Application\ProvideApplicationDirectory;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\User\HashPlainTextPassword;
use Peon\Domain\User\UsersCollection;
use Peon\Domain\Worker\WorkerStatusesCollection;
use Peon\Infrastructure\Cookbook\StaticRecipesCollection;
use Peon\Infrastructure\FileSystem\TemporaryLocalFileSystemProvideApplicationDirectory;
use Peon\Infrastructure\Git\PeonProvideBranchName;
use Peon\Infrastructure\GitLab\GitLab;
use Peon\Infrastructure\Job\DoctrineGetLongRunningJobs;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProcessesCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineProjectsCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineUsersCollection;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineWorkerStatusesCollection;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessRunProcess;
use Peon\Infrastructure\Project\DoctrineGetProjectIdentifiers;
use Peon\Infrastructure\Scheduler\DoctrineGetRecipeSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetTaskSchedules;
use Peon\Infrastructure\Symfony\ControllerArgumentValueResolvers\DomainIdArgumentValueResolver;
use Peon\Infrastructure\Symfony\ControllerArgumentValueResolvers\UserIdArgumentValueResolver;
use Peon\Infrastructure\Symfony\DependencyInjection\ConfigParameters;
use Peon\Infrastructure\User\SymfonyHashPlainTextPassword;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function(ContainerConfigurator $configurator): void
{
    $parameters = $configurator->parameters();

    # https://symfony.com/doc/current/performance.html#dump-the-service-container-into-a-single-file
    $parameters->set('container.dumper.inline_factories', true);

    $parameters->set(
        ConfigParameters::PEON_WORKING_DIRECTORIES_PATH,
        dirname(__DIR__, 4) . '/var/working_directories',
    );

    $parameters->set(
        ConfigParameters::HOST_WORKING_DIRECTORIES_PATH,
        env('HOST_WORKING_DIRECTORIES_PATH'),
    );

    $services = $configurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services->set(PsrLogMessageProcessor::class)
        ->tag('monolog.processor');

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

    $services->set(DomainIdArgumentValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 110]);

    $services->set(UserIdArgumentValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 110]);

    // Tools
    $services->load('Peon\\Domain\\Tools\\', __DIR__ . '/../../../Domain/Tools/**/{*.php}')
        ->exclude([
            __DIR__ . '/../../../Domain/Tools/**/{Exception,Value,Event}/*'
        ]);

    $services->set(TemporaryLocalFileSystemProvideApplicationDirectory::class)
        ->args([
            '$peonWorkingDirectoriesPath' => param(ConfigParameters::PEON_WORKING_DIRECTORIES_PATH),
            '$hostWorkingDirectoriesPath' => param(ConfigParameters::HOST_WORKING_DIRECTORIES_PATH),
        ]);

    $services->alias(ProvideApplicationDirectory::class, TemporaryLocalFileSystemProvideApplicationDirectory::class);

    $services->set(Git::class);
    $services->set(Composer::class);

    $services->alias(ProvideBranchName::class, PeonProvideBranchName::class);


    $services->set(DateTimeZone::class, DateTimeZone::class)->args(['UTC']);
    $services->set(SystemClock::class);
    $services->set(FrozenClock::class)->factory([FrozenClock::class, 'fromUTC']);
    $services->alias(Clock::class, SystemClock::class);

    $services->set(BuildPhpApplication::class);
    $services->set(PrepareApplicationGitRepository::class);

    $services->alias(GitProvider::class, GitLab::class);

    $services->alias(JobsCollection::class, DoctrineJobsCollection::class);

    $services->alias(TasksCollection::class, DoctrineTasksCollection::class);

    $services->alias(ProjectsCollection::class, DoctrineProjectsCollection::class);

    $services->alias(RecipesCollection::class, StaticRecipesCollection::class);

    $services->alias(ProcessesCollection::class, DoctrineProcessesCollection::class);

    $services->alias(UsersCollection::class, DoctrineUsersCollection::class);

    $services->alias(WorkerStatusesCollection::class, DoctrineWorkerStatusesCollection::class);

    $services->set(ExecuteCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(ExecuteCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(UpdateMergeRequest::class); // TODO: think how to do it automatically, it is not interface
    $services->set(SanitizeProcessCommand::class); // TODO: think how to do it automatically, it is not interface
    $services->set(SanitizeProcessOutput::class); // TODO: think how to do it automatically, it is not interface
    $services->set(GetPathsToProcess::class); // TODO: think how to do it automatically, it is not interface
    $services->set(DetectApplicationLanguage::class);
    $services->set(DetectContainerImage::class);
    $services->set(GetRecipeCommands::class);

    $services->set(ShouldSchedule::class); // TODO: think how to do it automatically, it is not interface
    $services->set(GetRecipeSchedules::class, DoctrineGetRecipeSchedules::class);
    $services->set(GetTaskSchedules::class, DoctrineGetTaskSchedules::class);
    $services->set(RunProcess::class, SymfonyProcessRunProcess::class);

    $services->set(DoctrineGetLongRunningJobs::class);
    $services->alias(GetLongRunningJobs::class, DoctrineGetLongRunningJobs::class);

    $services->set(SymfonyHashPlainTextPassword::class);
    $services->alias(HashPlainTextPassword::class, SymfonyHashPlainTextPassword::class);

    $services->alias(GetProjectIdentifiers::class, DoctrineGetProjectIdentifiers::class);

    $services->set(CheckUserAccess::class); // TODO: think how to do it automatically, it is not interface

    $services->set(PdoSessionHandler::class)
        ->args([
            env('DATABASE_URL'),
        ]);
};
