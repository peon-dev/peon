<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Event\RecipeDisabled;
use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DisableRecipeForProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private RecipesCollection $recipesCollection,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws ProjectNotFound
     * @throws RecipeNotFound
     */
    public function __invoke(DisableRecipeForProject $command): void
    {
        if ($this->recipesCollection->hasRecipeWithName($command->recipeName) === false) {
            throw new RecipeNotFound();
        }

        $project = $this->projectsCollection->get($command->projectId);

        $project->disableRecipe($command->recipeName);

        $this->projectsCollection->save($project);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new RecipeDisabled(
                $command->projectId,
                $command->recipeName,
            )
        );
    }
}
