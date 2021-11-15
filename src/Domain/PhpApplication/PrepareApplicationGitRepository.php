<?php

declare(strict_types=1);

namespace PHPMate\Domain\PhpApplication;

use PHPMate\Domain\Tools\Git\BranchNameProvider;
use PHPMate\Domain\Tools\Git\Git;
use PHPMate\Domain\Tools\Git\GitCommandFailed;
use Psr\Http\Message\UriInterface;

final class PrepareApplicationGitRepository // TODO: better naming
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

        if ($this->git->remoteBranchExists($applicationDirectory, $newBranch)) {
            $this->git->checkoutRemoteBranch($applicationDirectory, $newBranch);
        }

        $this->git->checkoutNewBranch($applicationDirectory, $newBranch);

        if ($this->git->remoteBranchExists($applicationDirectory, $newBranch)) {
            try {
                $this->git->rebaseBranchAgainstUpstream($applicationDirectory, $mainBranch);
                $this->git->forcePush($applicationDirectory);
            } catch (GitCommandFailed) {
                $this->git->abortRebase($applicationDirectory);
                $this->git->resetCurrentBranch($applicationDirectory, $mainBranch);
            }
        }

        return new LocalApplication($applicationDirectory, $mainBranch, $newBranch);
    }
}
