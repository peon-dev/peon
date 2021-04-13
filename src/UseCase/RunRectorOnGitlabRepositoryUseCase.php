<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\ProjectDirectoryProvider;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Git\Git;
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

        /*
         * TODO: what if MR by PHPMate for this procedure already exists?
         *
         * error: failed to push some refs to 'xyz'
         * hint: Updates were rejected because the tip of your current branch is behind
         * hint: its remote counterpart. Integrate the remote changes (e.g.
         * hint: 'git pull ...') before pushing again.
         * hint: See the 'Note about fast-forwards' in 'git push --help' for details.
         *
         * Options:
         *   - Comment to the MR (bump)
         *   - Checkout existing branch, run procedure and if changes, make commit
         *   - New fresh branch (duplicate)
         */

        $this->git->clone($projectDirectory, $command->gitlabRepository->getAuthenticatedRepositoryUri());

        $newBranch = $this->branchNameProvider->provideForProcedure('rector');

        // TODO flow:
        // 1) check if branch exists
        if ($this->git->remoteBranchExists($projectDirectory, $newBranch) === true) {

        }

        // 2) no: happy path
        // 3) yes: check if can rebase
        //   3a) adds new commit to rebased branch
        //   3b) new branch from master and force push
        // 4a) [optional] notify to slack
        // 4b) [optional] description with list of provided users

        // TODO: build application using buildpacks instead
        $this->composer->install($projectDirectory, $command->composerEnvironment);

        foreach ($command->processCommandConfigurations as $processCommandConfiguration) {
            // TODO: 3921/16612 [▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░░░]  23%Killed if process dies we need to know what is the reason!
            $this->rector->process($projectDirectory, $processCommandConfiguration);
        }

        if ($this->git->hasUncommittedChanges($projectDirectory)) {
            $mainBranch = $this->git->getCurrentBranch($projectDirectory);

            $this->git->checkoutNewBranch($projectDirectory, $newBranch);
            $this->git->commitAndPushChanges($projectDirectory, 'Rector changes');

            // TODO: [optional] assign to random user from provided list
            $this->gitlab->openMergeRequest(
                $command->gitlabRepository,
                $mainBranch,
                $newBranch,
                'Rector run by PHPMate'
            );
        }
    }
}
