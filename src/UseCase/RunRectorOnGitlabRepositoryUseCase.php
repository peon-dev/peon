<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabAuthentication;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryUseCase
{
    public function __construct(
        private Git $git,
        private Gitlab $gitlabApi,
        private Composer $composer,
        private Rector $rector
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        // TODO: Some service should provide this
        $workingDirectory = new Filesystem(
            new LocalFilesystemAdapter(__DIR__ . '/../../var')
        );

        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $gitlabRepository = GitlabRepository::createWithAuthentication($repositoryUri, $authentication);

        $this->git->clone($workingDirectory, $gitlabRepository->getAuthenticatedRepositoryUri());

        $this->composer->installInDirectory($workingDirectory);
        $this->rector->runInDirectory($workingDirectory);

        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($workingDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($workingDirectory, 'Changes by PHP Mate');

            $this->gitlabApi->openMergeRequest($gitlabRepository, $branchWithChanges);
        }
    }
}
