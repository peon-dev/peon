<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\EnabledRecipe;

final class ReadProjectDetail
{
    /**
     * @param array<EnabledRecipe> $enabledRecipes
     */
    public function __construct(
        public readonly string $name,
        public readonly string $projectId,
        /**
         * @var array<EnabledRecipe> $enabledRecipes
         */
        public readonly array $enabledRecipes,
        public readonly string $remoteGitRepositoryUri,
    ) {}


    public function hasRecipeBaseline(RecipeName $recipeName): bool
    {
        $recipe = $this->getEnabledRecipe($recipeName);

        if ($recipe === null) {
            return false;
        }

        return $recipe->baselineHash !== null;
    }


    public function isRecipeEnabled(RecipeName $recipeName): bool
    {
        return $this->getEnabledRecipe($recipeName) !== null;
    }


    private function getEnabledRecipe(RecipeName $recipeName): EnabledRecipe|null
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($recipeName === $enabledRecipe->recipeName) {
                return $enabledRecipe;
            }
        }

        return null;
    }
}
