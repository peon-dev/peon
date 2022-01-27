<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

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

        $this->git->clone($jobId, $applicationDirectory, $repositoryUri);
        $this->git->configureUser($jobId, $applicationDirectory);

        $mainBranch = $this->git->getCurrentBranch($jobId, $applicationDirectory);
        $taskBranch = $this->provideBranchName->forTask($taskName);

        $this->git->checkoutNewBranch($jobId, $applicationDirectory, $taskBranch);

        if ($this->git->remoteBranchExists($jobId, $applicationDirectory, $taskBranch)) {
            $this->git->trackRemoteBranch($jobId, $applicationDirectory, $taskBranch);
            $this->git->pull($jobId, $applicationDirectory);
            $this->syncWithHead($jobId, $applicationDirectory, $mainBranch);
        }

        return new TemporaryApplication($jobId, $applicationDirectory, $mainBranch, $taskBranch);
    }


    /**
     * @throws ProcessFailed
     */
    private function syncWithHead(JobId $jobId, string $applicationDirectory, string $mainBranch): void
    {
        try {
            $this->git->rebaseBranchAgainstUpstream($jobId, $applicationDirectory, $mainBranch);
            $this->git->forcePushWithLease($jobId, $applicationDirectory);
        } catch (ProcessFailed) {
            $this->git->abortRebase($jobId, $applicationDirectory);
            $this->git->resetCurrentBranch($jobId, $applicationDirectory, $mainBranch);
        }
    }
}
