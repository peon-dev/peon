<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase
{
    public function __construct(
        private Git $git,
        private Gitlab $gitlabApi,
        private Composer $composer,
        private Rector $rector
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        $directory = __DIR__ . '/../../var'; // TODO: Some service should provide this
        $repositoryName = ''; // TODO: Some service should extract repository name from remoteUri
        $remoteUri = ''; // TODO: some magic, add $username + $accesstoken into $remoteUri

        $this->git->clone($directory, $remoteUri);

        $this->composer->installInDirectory($directory);
        $this->rector->runInDirectory($directory);

        if ($this->git->hasUncommittedChanges($directory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($directory, $branchWithChanges);
            $this->git->commitChanges($directory, 'Changes by PHP Mate');

            $this->gitlabApi->openMergeRequest($repositoryName, $personalAccessToken, $branchWithChanges);
        }
    }
}
