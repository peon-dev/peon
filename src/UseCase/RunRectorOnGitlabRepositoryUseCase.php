<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nette\Utils\Strings;
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
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        $authentication = new GitlabAuthentication($username, $personalAccessToken);
        $gitlabRepository = new GitlabRepository($repositoryUri, $authentication);

        // TODO: temporary hack, create via some factory
        $dir = __DIR__ . '/../../var/' . Random::generate();
        FileSystem::createDir($dir);
        $workingDirectory = new WorkingDirectory($dir);

        $this->git->clone($workingDirectory, $gitlabRepository->getAuthenticatedRepositoryUri());

        $this->composer->installInDirectory($workingDirectory);
        $this->rector->runInDirectory($workingDirectory);

        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($workingDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($workingDirectory, 'Changes by PHP Mate');

            $this->gitlab->openMergeRequest($gitlabRepository, $branchWithChanges);
        }

        // TODO: cleanup
    }
}
