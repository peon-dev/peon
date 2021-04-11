<?php
declare (strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\FileSystem\ProjectDirectoryProvider;
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
        private ProjectDirectoryProvider $projectDirectoryProvider,
        private BranchNameProvider $branchNameProvider,
    ) {}


    public function __invoke(RunRectorOnGitlabRepository $command): void
    {
        $projectDirectory = $this->projectDirectoryProvider->provide();

        /*
         * TODO: what if MR by PHPMate for this procedure already exists?
         * Options:
         *   - Comment to the MR (bump)
         *   - Checkout existing branch, run procedure and if changes, make commit
         *   - New fresh branch (duplicate)
         */

        $this->git->clone($projectDirectory, $command->gitlabRepository->getAuthenticatedRepositoryUri());

        // TODO: build application using buildpacks instead
        $this->composer->install($projectDirectory);

        foreach ($this->getRectorWorkingDirectories($projectDirectory) as $rectorWorkingDirectory) {
            $this->rector->process($rectorWorkingDirectory);
        }

        if ($this->git->hasUncommittedChanges($projectDirectory)) {
            $mainBranch = $this->git->getCurrentBranch($projectDirectory);
            $branchWithChanges = $this->branchNameProvider->provideForProcedure('rector');

            $this->git->checkoutNewBranch($projectDirectory, $branchWithChanges);
            $this->git->commitAndPushChanges($projectDirectory, 'Rector changes');

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
    private function getRectorWorkingDirectories(WorkingDirectory $projectDirectory): array
    {
        $workingDirectories = [];

        if (count(self::$rectorWorkingDirectories) === 0) {
            $workingDirectories = [$projectDirectory];
        }

        foreach (self::$rectorWorkingDirectories as $rectorWorkingDirectory) {
            $subDirectory = $projectDirectory->getAbsolutePath() . '/' . $rectorWorkingDirectory;
            $workingDirectories[] = new WorkingDirectory($subDirectory);
        }

        return $workingDirectories;
    }
}
