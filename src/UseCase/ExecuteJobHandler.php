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
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessToProcessResultMapper;
use Peon\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class ExecuteJobHandler implements MessageHandlerInterface
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projects,
        private PrepareApplicationGitRepository $prepareApplicationGitRepository,
        private BuildApplication $buildApplication,
        private ProcessLogger $processLogger, // TODO: drop this dependency
        private Clock $clock,
        private RunJobCommands $runJobCommands,
        private RunJobRecipe $runJobRecipe,
        private UpdateMergeRequest $updateMergeRequest,
        private EventBus $eventBus,
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
                $remoteGitRepository->getAuthenticatedUri(),
                $jobTitle
            );

            $projectDirectory = $localApplication->workingDirectory;

            // 2. build application
            $this->buildApplication->build($projectDirectory);

            // 3. run commands
            if ($job->commands !== null) {
                $this->runJobCommands->run($job, $projectDirectory);
            }

            if ($job->enabledRecipe !== null) {
                $this->runJobRecipe->run($job->enabledRecipe, $projectDirectory);
            }

            // 4. merge request
            $mergeRequest = $this->updateMergeRequest->update($localApplication, $remoteGitRepository, $jobTitle);
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
            // TODO: Consider dropping collector pattern for something more clean?
            foreach ($this->processLogger->popLogs() as $processResult) {
                $job->addProcessResult($processResult);
            }

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
