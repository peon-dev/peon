<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\WorkingDirectory;
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
        private WorkingDirectory $workingDirectory
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $gitlabRepository = new GitlabRepository($repositoryUri, $authentication);

        $this->git->clone($this->workingDirectory, $gitlabRepository->getAuthenticatedRepositoryUri());

        $this->composer->installInDirectory($this->workingDirectory);
        $this->rector->runInDirectory($this->workingDirectory);

        if ($this->git->hasUncommittedChanges($this->workingDirectory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($this->workingDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($this->workingDirectory, 'Changes by PHP Mate');

            $this->gitlab->openMergeRequest($gitlabRepository, $branchWithChanges);
        }
    }
}
