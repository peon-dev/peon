<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Project\Exception\CouldNotConfigureDisabledRecipe;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ConfigureRecipeForProjectHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
    ) {}


    /**
     * @throws ProjectNotFound
     * @throws CouldNotConfigureDisabledRecipe
     */
    public function __invoke(ConfigureRecipeForProject $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);

        $configuration = new RecipeJobConfiguration($command->mergeAutomatically);
        $project->configureRecipe($command->recipeName, $configuration);

        $this->projectsCollection->save($project);
    }
}
