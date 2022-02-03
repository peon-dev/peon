<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Nette\Utils\JsonException;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\Tools\Rector\Rector;
use Peon\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;

class RunJobRecipe
{
    public function __construct(
        private Rector $rector,
        private Composer $composer,
        private Git $git,
    ) {}


    /**
     * @throws \RuntimeException
     */
    public function run(JobId $jobId, EnabledRecipe $enabledRecipe, string $workingDirectory): void
    {
        $paths = $this->getPathsToProcess($jobId, $enabledRecipe, $workingDirectory);

        if (count($paths) > 0) {
            $this->runSimpleRectorProcessCommandWithConfiguration($jobId, $workingDirectory, $enabledRecipe->recipeName, $paths);
        }
    }


    /**
     * @param array<string> $paths
     *
     * @throws ProcessFailed
     */
    private function runSimpleRectorProcessCommandWithConfiguration(
        JobId $jobId,
        string $workingDirectory,
        RecipeName $recipeName,
        array $paths,
    ): void
    {
        $configuration = new RectorProcessCommandConfiguration(
            autoloadFile: $workingDirectory . '/vendor/autoload.php', // TODO: this is weirdo
            config: __DIR__ . '/../../../vendor-bin/rector/config/' . $recipeName->value . '.php', // TODO: this is weirdo, think about better
            paths: $paths,
        );

        $this->rector->process($jobId, $workingDirectory, $configuration);
    }


    /**
     * @return array<string>
     *
     * @throws JsonException
     * @throws \RuntimeException
     */
    private function getPathsToProcess(JobId $jobId, EnabledRecipe $enabledRecipe, string $workingDirectory): array
    {
        if ($enabledRecipe->baselineHash !== null) {
            // TODO: maybe files should be in PSR-4 roots?
            return $this->git->getChangedFilesSinceCommit($jobId, $workingDirectory, $enabledRecipe->baselineHash);
        }

        $paths = $this->composer->getPsr4Roots($workingDirectory);

        if ($paths === null) {
            // TODO: change runtime exception to something else
            throw new \RuntimeException('PSR-4 roots must be defined to run this recipe!');
        }

        return $paths;
    }
}
