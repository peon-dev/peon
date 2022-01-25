<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Nette\Utils\JsonException;
use Peon\Domain\Cookbook\Value\RecipeName;
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
     * @throws ProcessFailed
     */
    public function run(EnabledRecipe $enabledRecipe, string $workingDirectory): void
    {

        try {
            $paths = $this->getPathsToProcess($enabledRecipe, $workingDirectory);

            $this->runSimpleRectorProcessCommandWithConfiguration($workingDirectory, $enabledRecipe->recipeName, $paths);
        } catch (\Throwable $throwable) {
            throw new ProcessFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @param array<string> $paths
     *
     * @throws \RuntimeException
     */
    private function runSimpleRectorProcessCommandWithConfiguration(
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

        $this->rector->process($workingDirectory, $configuration);
    }


    /**
     * @return array<string>
     *
     * @throws JsonException
     * @throws \RuntimeException
     */
    private function getPathsToProcess(EnabledRecipe $enabledRecipe, string $workingDirectory): array
    {
        $paths = $this->composer->getPsr4Roots($workingDirectory);

        if ($paths === null) {
            throw new \RuntimeException('PSR-4 roots must be defined to run this recipe!');
        }

        if ($enabledRecipe->baselineHash !== null) {
            // TODO: maybe files should be in PSR-4 roots?
            return $this->git->getChangedFilesSinceCommit($workingDirectory, $enabledRecipe->baselineHash);
        }

        return $paths;
    }
}
