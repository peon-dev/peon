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
        $workingDirectory = $this->projectDirectoryProvider->provide();

        $this->git->clone($jobId, $workingDirectory->localPath, $repositoryUri);
        $this->git->configureUser($jobId, $workingDirectory->localPath);

        $mainBranch = $this->git->getCurrentBranch($jobId, $workingDirectory->localPath);
        $taskBranch = $this->provideBranchName->forTask($taskName);

        $this->git->switchToBranch($jobId, $workingDirectory->localPath, $taskBranch);

        if ($this->git->remoteBranchExists($jobId, $workingDirectory->localPath, $taskBranch)) {
            $this->git->trackRemoteBranch($jobId, $workingDirectory->localPath, $taskBranch);
            $this->syncWithHead($jobId, $workingDirectory->localPath, $mainBranch);
        }

        return new TemporaryApplication($jobId, $workingDirectory, $mainBranch, $taskBranch);
    }


    /**
     * @throws ProcessFailed
     */
    private function syncWithHead(JobId $jobId, string $applicationDirectory, string $mainBranch): void
    {
        try {
            $this->git->pull($jobId, $applicationDirectory);
            $this->git->rebaseBranchAgainstUpstream($jobId, $applicationDirectory, $mainBranch);
        } catch (ProcessFailed) {
            $this->git->abortRebase($jobId, $applicationDirectory);
            $this->git->resetCurrentBranch($jobId, $applicationDirectory, $mainBranch);
        }

        $this->git->forcePushWithLease($jobId, $applicationDirectory);
    }
}
