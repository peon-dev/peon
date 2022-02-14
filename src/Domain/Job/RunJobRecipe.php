<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Rector\Rector;
use Peon\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;

class RunJobRecipe
{
    public function __construct(
        private Rector $rector,
        private GetPathsToProcess $getPathsToProcess,
    ) {}


    /**
     * @throws \RuntimeException
     */
    public function run(JobId $jobId, EnabledRecipe $enabledRecipe, string $workingDirectory): void
    {
        $paths = $this->getPathsToProcess->forJob($jobId, $enabledRecipe, $workingDirectory);

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
}
