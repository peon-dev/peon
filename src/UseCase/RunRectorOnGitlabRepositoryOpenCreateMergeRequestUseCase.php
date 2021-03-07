<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Application\Application;
use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\GitlabApi;
use PHPMate\Domain\Gitlab\GitlabRepository;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryOpenCreateMergeRequestUseCase
{
    public function __construct(
        private Git $git,
        private GitlabApi $gitlabApi,
        private Composer $composer,
        private Rector $rector
    ) {}


    public function __invoke(string $repositoryUri, string $username, string $personalAccessToken): void
    {
        $directory = ''; // TODO: Some service should provide this
        $repositoryName = ''; // TODO: Some service should extract repository name from remoteUri
        $remoteUri = ''; // TODO: some magic, add $username + $accesstoken into $remoteUri

        $this->git->clone($directory, $remoteUri);

        $application = Application::createFromDirectory($directory);

        $application->installComposer($this->composer);
        $application->runRector($this->rector);

        if ($this->git->hasUncommittedChanges($directory)) {
            $branchWithChanges = 'improvements';

            $this->git->checkoutNewBranch($directory, $branchWithChanges);
            $this->git->commitChanges($directory, 'Changes by PHP Mate');

            $gitlabRepository = GitlabRepository::fromPersonalAccessToken($repositoryName, $personalAccessToken);
            $gitlabRepository->openMergeRequest($branchWithChanges, $this->gitlabApi);
        }
    }
}
