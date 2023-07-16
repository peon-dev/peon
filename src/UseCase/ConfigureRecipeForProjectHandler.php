<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ConfigureRecipeForProjectHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ProjectsCollection $projectsCollection,
    ) {}


    /**
     * @throws ProjectNotFound
     * @throws RecipeNotEnabled
     */
    public function __invoke(ConfigureRecipeForProject $command): void
    {
        $project = $this->projectsCollection->get($command->projectId);

        $project->configureRecipe($command->recipeName, $command->configuration);

        $this->projectsCollection->save($project);
    }
}
