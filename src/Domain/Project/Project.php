<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\RecipeName;
use PHPMate\Domain\Project\Exceptions\RecipeAlreadyEnabledForProject;
use PHPMate\Domain\Project\Exceptions\RecipeNotEnabledForProject;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

class Project
{
    #[Immutable]
    public string $name;

    /**
     * @var array<RecipeName>
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $enabledRecipes = [];

    public function __construct(
        public ProjectId $projectId,
        public RemoteGitRepository $remoteGitRepository
    ) {
        $this->name = $this->remoteGitRepository->getProject();
    }


    /**
     * @throws RecipeAlreadyEnabledForProject
     */
    public function enableRecipe(RecipeName $recipe): void
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($enabledRecipe->isEqual($recipe)) {
                throw new RecipeAlreadyEnabledForProject();
            }
        }

        $this->enabledRecipes[] = $recipe;
    }


    /**
     * @throws RecipeNotEnabledForProject
     */
    public function disableRecipe(RecipeName $recipe): void
    {
        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->isEqual($recipe)) {
                unset($this->enabledRecipes[$key]);
                return;
            }
        }

        throw new RecipeNotEnabledForProject();
    }
}
