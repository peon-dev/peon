<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\Exceptions\RecipeAlreadyEnabledForProject;
use PHPMate\Domain\Cookbook\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Exceptions\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EnableRecipeForProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private RecipesCollection $recipesCollection
    ) {}


    /**
     * @throws ProjectNotFound
     * @throws RecipeNotFound
     * @throws RecipeAlreadyEnabledForProject
     */
    public function __invoke(EnableRecipeForProject $command): void
    {
        if ($this->recipesCollection->hasRecipeWithName($command->recipeName) === false) {
            throw new RecipeNotFound();
        }

        $project = $this->projectsCollection->get($command->projectId);

        $project->enableRecipe($command->recipeName);

        $this->projectsCollection->save($project);
    }
}
