<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Exception\NoPSR4RootsDefined;

class GetRecipeCommands
{
    public function __construct(
        private GetPathsToProcess $getPathsToProcess,
    ) {}


    /**
     * @return array<string>
     *
     * @throws ProcessFailed
     * @throws NoPSR4RootsDefined
     */
    public function forApplication(EnabledRecipe $enabledRecipe, TemporaryApplication $application): array
    {
        $paths = $this->getPathsToProcess->forJob(
            $application->jobId,
            $enabledRecipe,
            $application->gitRepository->workingDirectory->localPath,
        );

        if (count($paths) > 0) {
            // It will run in different docker container
            $command = sprintf(
                '/peon/bin/run-recipe %s %s',
                $enabledRecipe->recipeName->value,
                implode(' ', $paths),
            );

            return [$command];
        }

        return [];
    }
}
