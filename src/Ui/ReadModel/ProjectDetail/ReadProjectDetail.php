<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\ProjectDetail;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Value\GitProviderName;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\RecipeJobConfiguration;

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
        public readonly bool $skipComposerInstall,
        public readonly null|GitProviderName $gitProviderName,
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


    public function getRecipeBaselineHash(RecipeName $recipeName): null|string
    {
        return $this->getEnabledRecipe($recipeName)?->baselineHash;
    }


    public function getRecipeConfiguration(RecipeName $recipeName): null|RecipeJobConfiguration
    {
        return $this->getEnabledRecipe($recipeName)?->configuration;
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
