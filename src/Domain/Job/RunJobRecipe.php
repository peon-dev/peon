<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use Nette\Utils\JsonException;
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
    )
    {
    }


    /**
     * @throws ProcessFailed
     */
    public function run(RecipeName $recipeName, string $workingDirectory): void
    {
        try {
            $this->runSimpleRectorProcessCommandWithConfiguration($workingDirectory, $recipeName);
        } catch (\Throwable $throwable) {
            throw new ProcessFailed($throwable->getMessage(), previous: $throwable);
        }
    }


    /**
     * @throws JsonException
     * @throws \RuntimeException
     */
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
