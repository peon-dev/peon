<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class EnableRecipeForProjectHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ProjectsCollection $projectsCollection,
        private readonly RecipesCollection $recipesCollection,
        private readonly EventBus $eventBus,
    ) {}


    /**
     * @throws ProjectNotFound
     * @throws RecipeNotFound
     */
    public function __invoke(EnableRecipeForProject $command): void
    {
        if ($this->recipesCollection->hasRecipeWithName($command->recipeName) === false) {
            throw new RecipeNotFound();
        }

        $project = $this->projectsCollection->get($command->projectId);

        $project->enableRecipe($command->recipeName);

        $this->projectsCollection->save($project);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new RecipeEnabled(
                $command->projectId,
                $command->recipeName,
            )
        );
    }
}
