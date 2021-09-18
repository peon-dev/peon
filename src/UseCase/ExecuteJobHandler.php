<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\JobExecutionFailed;
use PHPMate\Domain\Job\JobHasNotStarted;
use PHPMate\Domain\Job\JobHasStartedAlready;
use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Process\ProcessFailed;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Tools\Composer\ComposerCommandFailed;
use PHPMate\Domain\PhpApplication\ApplicationDirectoryProvider;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\Tools\Git\GitCommandFailed;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectNotFound;
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
        private ApplicationDirectoryProvider $projectDirectoryProvider,
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
     * @throws JobHasNotStarted
     * @throws JobExecutionFailed
     */
    public function __invoke(ExecuteJob $command): void
    {
        $job = $this->jobsCollection->get($command->jobId);

        try {
            $project = $this->projects->get($job->projectId); // TODO: consider to drop dependency on project and pass only what is needed via command

            $job->start($this->clock);
            $this->jobsCollection->save($job);

            $remoteGitRepository = $project->remoteGitRepository;
            $localGitRepository = $this->prepareApplicationGitRepository->prepare(
                $remoteGitRepository->getAuthenticatedUri(),
                $job->taskName
            );
            $projectDirectory = $localGitRepository->workingDirectory;

            $this->buildApplication->build($projectDirectory);

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

            if ($this->git->hasUncommittedChanges($projectDirectory)) {
                $this->git->commit($projectDirectory, '[PHP Mate] Task ' . $job->taskName);
                $this->git->forcePush($projectDirectory);

                // $this->notifier->notifyAboutNewChanges(); // TODO: add test
                $branchWithChanges = $localGitRepository->jobBranch;

                if ($this->gitProvider->hasMergeRequestForBranch($remoteGitRepository, $branchWithChanges) === false) {
                    // TODO: [optional] assign to random user from provided list
                    // TODO: description with list of provided users
                    $this->gitProvider->openMergeRequest(
                        $remoteGitRepository,
                        $localGitRepository->mainBranch,
                        $branchWithChanges,
                        '[PHP Mate] Task ' . $job->taskName
                    );
                } // TODO: consider else branch (if MR already opened, that new commits were added)
            }

            // TODO: cover scenario when branch exists but merge request does not

            $job->succeeds($this->clock);
        } catch (JobHasStartedAlready $exception) {
            // TODO, im not sure what should happen
            // Do not fail the job, it might be already in progress
            // Maybe duplicate run
            // Maybe it already finished
            // Lets just throw
            throw $exception;
        } catch (\Throwable $throwable) {
            $job->fails($this->clock);

            // $this->notifier->notifyAboutFailedCommand($throwable);

            throw new JobExecutionFailed($throwable->getMessage(), previous: $throwable);
        } finally {
            // TODO: Consider dropping collector pattern for something more clean?
            foreach ($this->processLogger->getLogs() as $processResult) {
                $job->addProcessResult($processResult);
            }

            $this->jobsCollection->save($job);
        }
    }
}
