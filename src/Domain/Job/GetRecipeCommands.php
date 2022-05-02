<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Application\Value\TemporaryApplication;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Exception\NoPSR4RootsDefined;
use Peon\Domain\Tools\Rector\Rector;
use Peon\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;

class GetRecipeCommands
{
    public function __construct(
        private GetPathsToProcess $getPathsToProcess,
        private Rector $rector,
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
            $configuration = new RectorProcessCommandConfiguration(
                config: '/peon/vendor-bin/rector/config/' . $enabledRecipe->recipeName->value . '.php', // TODO: this is weirdo, think about better
                paths: $paths,
            );

            return [$this->rector->getProcessCommand($configuration)];
        }

        return [];
    }
}
