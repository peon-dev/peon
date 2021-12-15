<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Exception\RecipeAlreadyEnabledForProject;
use PHPMate\Domain\Project\Exception\RecipeNotEnabledForProject;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Project\Value\RecipeBaseline;

class Project
{
    #[Immutable]
    public string $name;

    /**
     * @var array<RecipeBaseline>
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $baselines = [];

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
     * @param array<RecipeName> $recipes
     * @deprecated
     */
    public function changeRecipes(array $recipes): void
    {
        $this->enabledRecipes = $recipes;
    }


    /**
     * @throws RecipeAlreadyEnabledForProject
     */
    public function enableRecipe(RecipeName $recipe): void
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($enabledRecipe->equals($recipe)) {
                throw new RecipeAlreadyEnabledForProject();
            }
        }

        $this->enabledRecipes[] = $recipe;
    }


    /**
     * @throws RecipeAlreadyEnabledForProject
     */
    public function enableRecipeWithBaseline(RecipeName $recipeName, string $baselineHash): void
    {
        $this->enableRecipe($recipeName);

        foreach ($this->baselines as $baseline) {
            if ($baseline->recipeName->equals($recipeName)) {
                throw new RecipeAlreadyEnabledForProject();
            }
        }

        $this->baselines[] = new RecipeBaseline($recipeName, $baselineHash);
    }


    /**
     * @throws RecipeNotEnabledForProject
     */
    public function disableRecipe(RecipeName $recipe): void
    {
        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->equals($recipe)) {
                unset($this->enabledRecipes[$key]);

                // Recipe was enabled, check if it has baseline
                foreach ($this->baselines as $baselineKey => $baseline) {
                    if ($baseline->recipeName->equals($recipe)) {
                        unset($this->baselines[$baselineKey]);
                    }
                }

                return;
            }
        }

        throw new RecipeNotEnabledForProject();
    }


    public function isRecipeEnabled(RecipeName $recipe): bool
    {
        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->equals($recipe)) {
                return true;
            }
        }

        return false;
    }
}
