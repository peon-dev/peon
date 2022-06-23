<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Application\DetectApplicationLanguage;
use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Application\Value\WorkingDirectory;
use Peon\Domain\Container\DetectContainerImage;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\GetRecipeCommands;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\PhpApplication\BuildPhpApplication;
use Peon\Domain\Application\PrepareApplicationGitRepository;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Tools\Composer\Exception\NoPSR4RootsDefined;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Filesystem\Filesystem;

final class ExecuteJobHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly JobsCollection                  $jobsCollection,
        private readonly ProjectsCollection              $projects,
        private readonly PrepareApplicationGitRepository $prepareApplicationGitRepository,
        private readonly BuildPhpApplication             $buildApplication,
        private readonly Clock                           $clock,
        private readonly UpdateMergeRequest              $updateMergeRequest,
        private readonly EventBus                        $eventBus,
        private readonly ExecuteCommand                  $executeCommand,
        private readonly DetectApplicationLanguage       $detectApplicationLanguage,
        private readonly GetRecipeCommands               $getRecipeCommands,
        private readonly DetectContainerImage $detectContainerImage,
    ) {}


    /**
     * @throws JobNotFound
     * @throws JobHasStartedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasFinishedAlready
     * @throws JobExecutionFailed
     */
    public function __invoke(ExecuteJob $command): void
    {
        $job = $this->jobsCollection->get($command->jobId);
        $mergeRequest = null;

        try {
            $project = $this->projects->get($job->projectId);
            $jobTitle = $job->title;
            $remoteGitRepository = $project->remoteGitRepository;

            $job->start($this->clock);
            $this->jobsCollection->save($job);

            // TODO: this event could be dispatched in entity
            $this->eventBus->dispatch(
                new JobStatusChanged(
                    $job->jobId,
                    $job->projectId,
                )
            );

            // 1. Prepare (clone + config) git repository
            $localGitRepository = $this->prepareApplicationGitRepository->forRemoteRepository(
                $job->jobId,
                $remoteGitRepository->getAuthenticatedUri(),
                $jobTitle
            );

            // 2. Detect language
            $language = $this->detectLanguageAndUpdateProjectIfLanguageDiffers(
                $localGitRepository->workingDirectory,
                $project,
            );

            $application = new TemporaryApplication(
                $job->jobId,
                $language,
                $localGitRepository,
            );

            // 3. Build application
            $this->buildApplication->build($application, $project->buildConfiguration);

            // 4. Execute commands
            $this->executeJobCommandsInIsolation($job, $application);

            // 5. Merge request
            $mergeRequest = $this->updateMergeRequest->update(
                $job->jobId,
                $localGitRepository,
                $remoteGitRepository,
                $jobTitle,
                $command->mergeAutomatically,
            );

            $job->succeeds($this->clock, $mergeRequest);
        } catch (JobHasStartedAlready $exception) {
            // TODO, im not sure what should happen
            // Do not fail the job, it might be already in progress
            // Maybe duplicate run
            // Maybe it already finished
            // Lets just throw
            throw $exception;
        } catch (ProjectNotFound) {
            $job->cancel($this->clock);
        } catch (\Throwable $throwable) {
            $job->fails($this->clock, $mergeRequest);

            throw new JobExecutionFailed($throwable->getMessage(), previous: $throwable);
        } finally {
            $this->jobsCollection->save($job);

            // TODO: this event could be dispatched in entity
            $this->eventBus->dispatch(
                new JobStatusChanged(
                    $job->jobId,
                    $job->projectId,
                )
            );

            // 6. Post-job working directory cleanup - delete and maybe cache in the future
            if (isset($localGitRepository)) {
                // TODO: this is really just an hot fix and should be handled in a better way
                $filesystem = new Filesystem();
                if ($filesystem->exists($localGitRepository->workingDirectory->localPath)) {
                    $filesystem->remove($localGitRepository->workingDirectory->localPath);
                }
            }
        }
    }


    private function detectLanguageAndUpdateProjectIfLanguageDiffers(
        WorkingDirectory $workingDirectory,
        Project $project,
    ): ApplicationLanguage
    {
        $language = $this->detectApplicationLanguage->inDirectory($workingDirectory);

        if ($language->isSameAs($project->language) === false) {
            $project->updateLanguage($language);

            $this->projects->save($project);
        }

        return $language;
    }


    /**
     * @throws ProcessFailed
     * @throws NoPSR4RootsDefined
     */
    private function executeJobCommandsInIsolation(Job $job, TemporaryApplication $application): void
    {
        $image = $this->detectContainerImage->forLanguage($application->language);
        $commands = [];

        if ($job->commands !== null) {
            $commands = $job->commands;
        }

        if ($job->enabledRecipe !== null) {
            $commands = $this->getRecipeCommands->forApplication($job->enabledRecipe, $application);
        }

        foreach ($commands as $command) {
            $this->executeCommand->inContainer(
                $job->jobId,
                $image,
                $application->gitRepository->workingDirectory->hostPath,
                $command,
            );
        }
    }
}
