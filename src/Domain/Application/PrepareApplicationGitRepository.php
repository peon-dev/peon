<?php

declare(strict_types=1);

namespace Peon\Domain\Application;

use Peon\Domain\Application\DetectApplicationLanguage;
use Peon\Domain\Application\Value\ApplicationGitRepositoryClone;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Application\ProvideApplicationDirectory;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Tools\Git\ProvideBranchName;
use Peon\Domain\Tools\Git\Git;
use Psr\Http\Message\UriInterface;

class PrepareApplicationGitRepository
{
    public function __construct(
        private Git                         $git,
        private ProvideApplicationDirectory $projectDirectoryProvider,
        private ProvideBranchName           $provideBranchName,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function forRemoteRepository(JobId $jobId, UriInterface $repositoryUri, string $taskName): ApplicationGitRepositoryClone
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

        return new ApplicationGitRepositoryClone(
            $workingDirectory,
            $mainBranch,
            $taskBranch,
        );
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
