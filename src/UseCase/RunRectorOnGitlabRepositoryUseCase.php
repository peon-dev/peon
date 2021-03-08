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
        $directory = __DIR__ . '/../../var';
        $filesystem = new Filesystem(
            new LocalFilesystemAdapter($directory)
        );

        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $gitlabRepository = GitlabRepository::createWithAuthentication($repositoryUri, $authentication);

        $this->git->clone($directory, $gitlabRepository->getAuthenticatedRepositoryUri());

        $this->composer->installInDirectory($filesystem);
        $this->rector->runInDirectory($directory);

        if ($this->git->hasUncommittedChanges($directory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($directory, $branchWithChanges);
            $this->git->commitAndPushChanges($directory, 'Changes by PHP Mate');

            $this->gitlabApi->openMergeRequest($gitlabRepository, $branchWithChanges);
        }
    }
}
