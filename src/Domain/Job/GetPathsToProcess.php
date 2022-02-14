<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Composer\Exception\NoPSR4RootsDefined;
use Peon\Domain\Tools\Git\Git;

class GetPathsToProcess
{
    public function __construct(
        private Git $git,
        private Composer $composer,
    ) {}


    /**
     * @return array<string>
     * @throws NoPSR4RootsDefined
     * @throws ProcessFailed
     */
    public function forJob(JobId $jobId, EnabledRecipe $enabledRecipe, string $workingDirectory): array
    {
        $psr4Roots = $this->composer->getPsr4Roots($workingDirectory);

        if ($psr4Roots === null) {
            throw new NoPSR4RootsDefined();
        }

        if ($enabledRecipe->baselineHash !== null) {
            $changedFiles = $this->git->getChangedFilesSinceCommit($jobId, $workingDirectory, $enabledRecipe->baselineHash);

            return $this->filterChangedFilesOutsidePsr4Roots($changedFiles, $psr4Roots);
        }

        return $psr4Roots;
    }


    /**
     * @param array<string> $changedFiles
     * @param array<string> $psr4Roots
     * @return array<string>
     */
    private function filterChangedFilesOutsidePsr4Roots(array $changedFiles, array $psr4Roots): array
    {
        return array_filter($changedFiles, static function(string $changedFile) use ($psr4Roots) {
            foreach ($psr4Roots as $psr4root) {
                if (str_starts_with($changedFile, $psr4root)) {
                    return true;
                }
            }

            return false;
        });
    }
}
