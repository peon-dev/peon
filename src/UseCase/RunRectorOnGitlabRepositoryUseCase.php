<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\FileSystem\WorkingDirectoryProvider;
use PHPMate\Domain\Git\BranchNameProvider;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Rector\Rector;

final class RunRectorOnGitlabRepositoryUseCase
{
    /**
     * @var string[]
     */
    public static array $rectorWorkingDirectories = [];

    public function __construct(
        private Git $git,
        private Gitlab $gitlab,
        private Composer $composer,
        private Rector $rector,
        private WorkingDirectoryProvider $workingDirectoryProvider,
        private BranchNameProvider $branchNameProvider,
    ) {}


    public function __invoke(RunRectorOnGitlabRepository $command): void
    {
        $workingDirectory = $this->workingDirectoryProvider->provide();

        /*
         * TODO: what if MR by PHPMate for this procedure already exists?
         * Options:
         *   - Comment to the MR (bump)
         *   - Checkout existing branch, run procedure and if changes, make commit
         *   - New fresh branch (duplicate)
         */

        $this->git->clone($workingDirectory, $command->gitlabRepository->getAuthenticatedRepositoryUri());

        // TODO: build application using buildpacks instead
        $this->composer->installInWorkingDirectory($workingDirectory);

        foreach ($this->getRectorWorkingDirectories($workingDirectory) as $rectorWorkingDirectory) {
            $this->rector->runInWorkingDirectory($rectorWorkingDirectory);
        }

        if ($this->git->hasUncommittedChanges($workingDirectory)) {
            $mainBranch = $this->git->getCurrentBranch($workingDirectory);
            $branchWithChanges = $this->branchNameProvider->provideForProcedure('rector');

            $this->git->checkoutNewBranch($workingDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($workingDirectory, 'Rector changes');

            $this->gitlab->openMergeRequest(
                $command->gitlabRepository,
                $mainBranch,
                $branchWithChanges,
                'Rector run by PHPMate'
            );
        }
    }


    /**
     * @return WorkingDirectory[]
     */
    private function getRectorWorkingDirectories(WorkingDirectory $workingDirectory): array
    {
        $workingDirectories = [];

        if (count(self::$rectorWorkingDirectories) === 0) {
            $workingDirectories = [$workingDirectory];
        }

        foreach (self::$rectorWorkingDirectories as $rectorWorkingDirectory) {
            $subDirectory = $workingDirectory->getAbsolutePath() . '/' . $rectorWorkingDirectory;
            $workingDirectories[] = new WorkingDirectory($subDirectory);
        }

        return $workingDirectories;
    }
}
