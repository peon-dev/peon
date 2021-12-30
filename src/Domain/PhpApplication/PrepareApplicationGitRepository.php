<?php

declare(strict_types=1);

namespace PHPMate\Domain\PhpApplication;

use PHPMate\Domain\PhpApplication\Value\LocalApplication;
use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use Psr\Http\Message\UriInterface;

class PrepareApplicationGitRepository // TODO: better naming
{
    public function __construct(
        private Git $git,
        private ApplicationDirectoryProvider $projectDirectoryProvider,
        private BranchNameProvider $branchNameProvider,
    ) {}


    /**
     * @throws GitCommandFailed
     */
    public function prepare(UriInterface $repositoryUri, string $taskName): LocalApplication
    {
        $applicationDirectory = $this->projectDirectoryProvider->provide();

        $this->git->clone($applicationDirectory, $repositoryUri);
        $this->git->configureUser($applicationDirectory);

        $mainBranch = $this->git->getCurrentBranch($applicationDirectory);
        $newBranch = $this->branchNameProvider->provideForTask($taskName);

        $this->git->checkoutNewBranch($applicationDirectory, $newBranch);

        if ($this->git->remoteBranchExists($applicationDirectory, $newBranch)) {
            $this->git->trackRemoteBranch($applicationDirectory, $newBranch);
            $this->git->pull($applicationDirectory);
            $this->syncWithHead($applicationDirectory, $mainBranch);
        }

        return new LocalApplication($applicationDirectory, $mainBranch, $newBranch);
    }


    /**
     * @throws GitCommandFailed
     */
    private function syncWithHead(string $applicationDirectory, string $mainBranch): void
    {
        try {
            $this->git->rebaseBranchAgainstUpstream($applicationDirectory, $mainBranch);
            $this->git->forcePushWithLease($applicationDirectory);
        } catch (GitCommandFailed) {
            $this->git->abortRebase($applicationDirectory);
            $this->git->resetCurrentBranch($applicationDirectory, $mainBranch);
        }
    }
}
