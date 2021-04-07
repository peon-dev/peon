<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\WorkingDirectoryProvider;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryUseCase
{
    public function __construct(
        private Git $git,
        private Gitlab $gitlab,
        private Composer $composer,
        private Rector $rector,
        private WorkingDirectoryProvider $workingDirectoryProvider,
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $gitlabRepository = new GitlabRepository($repositoryUri, $authentication);
        $workingDirectory = $this->workingDirectoryProvider->provide();

        /*
         * TODO: what if MR by PHPMate for this procedure already exists?
         * Options:
         *   - Comment to the MR (bump)
         *   - Checkout existing branch, run procedure and if changes, make commit
         *   - New fresh branch (duplicate)
         */

        $this->git->clone($workingDirectory, $gitlabRepository->getAuthenticatedRepositoryUri());

        $this->composer->installInWorkingDirectory($workingDirectory);
        $this->rector->runInWorkingDirectory($workingDirectory);

        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $mainBranch = $this->git->getCurrentBranch($workingDirectory);
            $branchWithChanges = 'improvements'; // TODO: dynamic

            $this->git->checkoutNewBranch($workingDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($workingDirectory, 'Rector changes');

            $this->gitlab->openMergeRequest(
                $gitlabRepository,
                $mainBranch,
                $branchWithChanges,
                'Rector run by PHPMate'
            );
        }
    }
}
