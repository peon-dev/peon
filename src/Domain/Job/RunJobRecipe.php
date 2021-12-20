<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Process\Exception\ProcessFailed;
use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;

class RunJobRecipe
{
    public function __construct(
        private Rector $rector,
        private Composer $composer,
        private RecipesCollection $recipesCollection,
    )
    {
    }


    /**
     * @throws ProcessFailed
     */
    public function run(Job $job, string $workingDirectory): void
    {
        assert($job->recipeName !== null);

        $recipeName = RecipeName::from($job->recipeName);

        $this->runSimpleRectorProcessCommandWithConfiguration($workingDirectory, $recipeName);
    }


    private function runSimpleRectorProcessCommandWithConfiguration(
        string $workingDirectory,
        RecipeName $recipeName
    ): void
    {
        $paths = $this->composer->getPsr4Roots($workingDirectory);

        if ($paths === null) {
            throw new \RuntimeException('PSR-4 roots must be defined to run this recipe!');
        }

        $configuration = new RectorProcessCommandConfiguration(
            autoloadFile: $workingDirectory . '/vendor/autoload.php',
            config: __DIR__ . '/../../../vendor-bin/rector/config/' . $recipeName->value . '.php',
            paths: $paths,
        );

        $this->rector->process($workingDirectory, $configuration);
    }
}
