<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

use Peon\Domain\Job\Job;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\Value\TemporaryApplication;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Domain\Tools\Git\Git;
use Psr\Http\Message\UriInterface;

class PrepareApplicationGitRepository // TODO: better naming
{
    public function __construct(
        private Git                         $git,
        private ProvideApplicationDirectory $projectDirectoryProvider,
        private ProvideBranchName           $provideBranchName,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function prepare(JobId $jobId, UriInterface $repositoryUri, string $taskName): TemporaryApplication
    {
        $applicationDirectory = $this->projectDirectoryProvider->provide();

        $this->git->clone($job, $applicationDirectory, $repositoryUri);
        $this->git->configureUser($job, $applicationDirectory);

        $mainBranch = $this->git->getCurrentBranch($job, $applicationDirectory);
        $taskBranch = $this->provideBranchName->forTask($taskName);

        $this->git->checkoutNewBranch($job, $applicationDirectory, $taskBranch);

        if ($this->git->remoteBranchExists($job, $applicationDirectory, $taskBranch)) {
            $this->git->trackRemoteBranch($job, $applicationDirectory, $taskBranch);
            $this->git->pull($job, $applicationDirectory);
            $this->syncWithHead($job, $applicationDirectory, $mainBranch);
        }

        return new TemporaryApplication($jobId, $applicationDirectory, $mainBranch, $taskBranch);
    }


    /**
     * @throws ProcessFailed
     */
    private function syncWithHead(Job $job, string $applicationDirectory, string $mainBranch): void
    {
        try {
            $this->git->rebaseBranchAgainstUpstream($job, $applicationDirectory, $mainBranch);
            $this->git->forcePushWithLease($job, $applicationDirectory);
        } catch (ProcessFailed) {
            $this->git->abortRebase($job, $applicationDirectory);
            $this->git->resetCurrentBranch($job, $applicationDirectory, $mainBranch);
        }
    }
}
