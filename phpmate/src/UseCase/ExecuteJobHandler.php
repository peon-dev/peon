<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\PhpApplication\BuildApplication;
use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPMate\Domain\Tools\Composer\ComposerCommandFailed;
use PHPMate\Domain\PhpApplication\ApplicationDirectoryProvider;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\Tools\Git\GitCommandFailed;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\PrepareProjectApplicationForJob;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;

final class ExecuteJobHandler
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projects,
        private ApplicationDirectoryProvider $projectDirectoryProvider,
        private PrepareApplicationGitRepository $prepareApplicationGitRepository,
        private BuildApplication $buildApplication,
        private Git $git,
        private GitProvider $gitProvider
    ) {}


    /**
     * @throws JobNotFound
     * @throws ProjectNotFound
     * @throws GitCommandFailed
     * @throws ComposerCommandFailed
     */
    public function handle(ExecuteJob $useCase): void
    {
        $job = $this->jobsCollection->get($useCase->jobId);
        $project = $this->projects->get($job->projectId);

        try {
            $job->start();
            $this->jobsCollection->save($job);

            $remoteGitRepository = $project->getGitRepository();
            $localGitRepository = $this->prepareApplicationGitRepository->prepare(
                $remoteGitRepository->getAuthenticatedUri(),
                $job->taskName
            );
            $projectDirectory = $localGitRepository->workingDirectory;

            $this->buildApplication->build($projectDirectory);

            foreach ($job->commands as $command) {
                // run process with script
            }

            if ($this->git->hasUncommittedChanges($projectDirectory)) {
                $this->git->commit($projectDirectory, '[PHP Mate] Task ' . $job->taskName);
                $this->git->forcePush($projectDirectory);

                // $this->notifier->notifyAboutNewChanges(); // TODO: add test
            }

            $mainBranch = $localGitRepository->mainBranch;
            $branchWithChanges = $localGitRepository->jobBranch;

            if ($this->gitProvider->hasMergeRequestForBranch($remoteGitRepository, $branchWithChanges) === false) {
                // TODO: [optional] assign to random user from provided list
                // TODO: description with list of provided users
                $this->gitProvider->openMergeRequest(
                    $remoteGitRepository,
                    $mainBranch,
                    $branchWithChanges,
                    '[PHP Mate] Task ' . $job->taskName
                );
            }

            $job->finish();
            $this->jobsCollection->save($job);
        } catch (\Throwable $throwable) {
            $job->fail();
            $this->jobsCollection->save($job);

            // $this->notifier->notifyAboutFailedCommand($throwable);

            throw $throwable;
        }

    }
}
