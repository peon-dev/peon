<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exception\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exception\JobHasStartedAlready;
use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Process\Exception\ProcessFailed;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Infrastructure\Process\Symfony\SymfonyProcessToProcessResultMapper;
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
        private Git $git,
        private GitProvider $gitProvider,
        private ProcessLogger $processLogger,
        private Clock $clock,
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

            // 1. Prepare git (clone) repository to local application
            $localApplication = $this->prepareApplicationGitRepository->prepare(
                $remoteGitRepository->getAuthenticatedUri(),
                $jobTitle
            );

            $projectDirectory = $localApplication->workingDirectory;

            // 2. build application
            $this->buildApplication->build($projectDirectory);

            // 3. run commands
            foreach ($job->commands as $jobCommand) {
                // TODO: decouple
                $process = Process::fromShellCommandline($jobCommand, $projectDirectory, timeout: 60 * 20);

                try {
                    $process->mustRun();
                } catch (ProcessFailedException $processFailedException) {
                    $process = $processFailedException->getProcess();

                    throw new ProcessFailed($processFailedException->getMessage(), previous: $processFailedException);
                } finally {
                    $processResult = SymfonyProcessToProcessResultMapper::map($process);

                    $this->processLogger->logResult($processResult);
                }
            }

            // 4. merge request
            $branchWithChanges = $localApplication->jobBranch;
            $mergeRequest = $this->gitProvider->getMergeRequestForBranch($remoteGitRepository, $branchWithChanges);

            // Let's see if job changed something
            if ($this->git->hasUncommittedChanges($projectDirectory)) {
                $this->git->commit($projectDirectory, '[PHP Mate] ' . $jobTitle);
                $this->git->forcePush($projectDirectory);

                // Great, we have changed code and MR was not opened yet
                if ($mergeRequest === null) {
                    $mergeRequest = $this->gitProvider->openMergeRequest(
                        $remoteGitRepository,
                        $localApplication->mainBranch,
                        $branchWithChanges,
                        '[PHP Mate] ' . $jobTitle
                    );
                }
            } elseif ($this->git->remoteBranchExists($projectDirectory, $localApplication->jobBranch)) {
                if ($mergeRequest === null) {
                    $mergeRequest = $this->gitProvider->openMergeRequest(
                        $remoteGitRepository,
                        $localApplication->mainBranch,
                        $localApplication->jobBranch,
                        '[PHP Mate] ' . $jobTitle
                    );
                }
            }

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
        }
    }
}
