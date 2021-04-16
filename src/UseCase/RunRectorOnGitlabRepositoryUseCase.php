<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\ProjectDirectoryProvider;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\RebaseFailed;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryUseCase
{
    public function __construct(
        private Git $git,
        private Gitlab $gitlab,
        private Composer $composer,
        private Rector $rector,
        private ProjectDirectoryProvider $projectDirectoryProvider,
        private BranchNameProvider $branchNameProvider,
    ) {}


    public function __invoke(RunRectorOnGitlabRepository $command): void
    {
        $projectDirectory = $this->projectDirectoryProvider->provide();

        // TODO: add caching of git repo
        $this->git->clone($projectDirectory, $command->gitlabRepository->getAuthenticatedRepositoryUri());
        $this->git->configureUser($projectDirectory);

        $mainBranch = $this->git->getCurrentBranch($projectDirectory);
        $newBranch = $this->branchNameProvider->provideForProcedure('rector');

        if ($this->git->remoteBranchExists($projectDirectory, $newBranch)) {
            $this->git->checkoutRemoteBranch($projectDirectory, $newBranch);
        }

        $this->git->checkoutNewBranch($projectDirectory, $newBranch);

        if ($this->git->remoteBranchExists($projectDirectory, $newBranch)) {
            try {
                $this->git->rebaseBranchAgainstUpstream($projectDirectory, $mainBranch);
                $this->git->forcePush($projectDirectory);
            } catch (RebaseFailed) {
                $this->git->abortRebase($projectDirectory);
                $this->git->resetCurrentBranch($projectDirectory, $mainBranch);
            }
        }

        // TODO: build application using buildpacks instead
        $this->composer->install($projectDirectory, $command->composerEnvironment);

        foreach ($command->processCommandConfigurations as $processCommandConfiguration) {
            // TODO: 3921/16612 [▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░░]  23%Killed if process dies we need to know what is the reason!
            $this->rector->process($projectDirectory, $processCommandConfiguration);
        }

        if ($this->git->hasUncommittedChanges($projectDirectory)) {
            $this->git->commit($projectDirectory, 'Rector changes');
            $this->git->forcePush($projectDirectory);
        }

        if ($this->gitlab->mergeRequestForBranchExists($command->gitlabRepository, $newBranch) === false) {
            // TODO: notify to slack if configured

            // TODO: [optional] assign to random user from provided list
            // TODO: description with list of provided users
            $this->gitlab->openMergeRequest(
                $command->gitlabRepository,
                $mainBranch,
                $newBranch,
                'Rector run by PHPMate'
            );
        }
    }
}
