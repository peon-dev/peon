<?php
declare (strict_types=1);

namespace PHPMate\Worker\UseCase;

use PHPMate\Worker\Domain\Composer\Composer;
use PHPMate\Worker\Domain\Composer\ComposerCommandFailed;
use PHPMate\Worker\Domain\FileSystem\ProjectDirectoryProvider;
use PHPMate\Worker\Domain\Git\BranchNameProvider;
use PHPMate\Worker\Domain\Git\Git;
use PHPMate\Worker\Domain\Git\GitCommandFailed;
use PHPMate\Worker\Domain\Git\RebaseFailed;
use PHPMate\Worker\Domain\Gitlab\Gitlab;
use PHPMate\Worker\Domain\Notification\Notifier;
use PHPMate\Worker\Domain\Rector\Rector;
use PHPMate\Worker\Domain\Rector\RectorCommandFailed;

class RunRectorOnGitlabRepositoryUseCase
{
    public function __construct(
        private Git $git,
        private Gitlab $gitlab,
        private Composer $composer,
        private Rector $rector,
        private ProjectDirectoryProvider $projectDirectoryProvider,
        private BranchNameProvider $branchNameProvider,
        private Notifier $notifier
    ) {}


    public function __invoke(RunRectorOnGitlabRepository $command): void
    {
        $projectDirectory = $this->projectDirectoryProvider->provide();

        // TODO: add caching of git repo
        // Scenario:
        //   - Check if cache exists:
        //
        //   NO:
        //   - clone
        //
        //   YES:
        //   - retrieve from cache
        //   - fetch
        //   - pull
        //
        //   ... continue ...
        //
        //   - Save to cache after MR

        try {
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
                $this->notifier->notifyAboutNewChanges(); // TODO: add test
            }

            if ($this->gitlab->mergeRequestForBranchExists($command->gitlabRepository, $newBranch) === false) {
                // TODO: [optional] assign to random user from provided list
                // TODO: description with list of provided users
                $this->gitlab->openMergeRequest(
                    $command->gitlabRepository,
                    $mainBranch,
                    $newBranch,
                    'Rector run by PHPMate'
                );
            }

            //
        } catch (GitCommandFailed | ComposerCommandFailed | RectorCommandFailed $exception) {
            $this->notifier->notifyAboutFailedCommand($exception);

            // TODO: What about event driven dev? Event sourcing could be great here, dispatch something happened :-)

            // Rethrow .. maybe use $this->logger->log() instead?
            // but logging in domain feels weird...
            throw $exception;
        }
    }
}
