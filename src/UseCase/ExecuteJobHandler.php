<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\RunJobCommands;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\UpdateMergeRequest;
use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\PrepareApplicationGitRepository;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class ExecuteJobHandler implements CommandHandlerInterface
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projects,
        private PrepareApplicationGitRepository $prepareApplicationGitRepository,
        private BuildApplication $buildApplication,
        private Clock $clock,
        private RunJobCommands $runJobCommands,
        private RunJobRecipe $runJobRecipe,
        private UpdateMergeRequest $updateMergeRequest,
        private EventBus $eventBus,
        private ExecuteCommand $executeCommand,
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

            // 1. Prepare git (clone) repository to local application
            $localApplication = $this->prepareApplicationGitRepository->prepare(
                $job,
                $remoteGitRepository->getAuthenticatedUri(),
                $jobTitle
            );

            $projectDirectory = $localApplication->workingDirectory;

            // 2. build application
            $this->buildApplication->build($job, $projectDirectory, $project->buildConfiguration);

            // 3a. run commands
            if ($job->commands !== null) {
                foreach ($job->commands as $jobCommand) {
                    $this->executeCommand->inDirectory($job, $projectDirectory, $jobCommand);
                }
            }

            // 3b. or run recipe
            if ($job->enabledRecipe !== null) {
                $this->runJobRecipe->run($job->enabledRecipe, $projectDirectory);
            }

            // 4. merge request
            $mergeRequest = $this->updateMergeRequest->update($job, $localApplication, $remoteGitRepository, $jobTitle);
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
        }
    }
}
