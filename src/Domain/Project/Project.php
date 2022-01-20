<?php

declare(strict_types=1);

namespace Peon\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Project\Value\EnabledRecipe;

class Project
{
    public readonly string $name;

    /**
     * @var array<EnabledRecipe>
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $enabledRecipes = [];

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public BuildConfiguration $buildConfiguration;

    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RemoteGitRepository $remoteGitRepository
    ) {
        $this->name = $this->remoteGitRepository->getProject();
        $this->buildConfiguration = BuildConfiguration::createDefault();
    }


    public function getEnabledRecipe(RecipeName $recipeName): EnabledRecipe|null
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($enabledRecipe->recipeName === $recipeName) {
                return $enabledRecipe;
            }
        }

        return null;
    }


    public function enableRecipe(RecipeName $recipeName, string|null $baseline = null): void
    {
        $recipeToBeEnabled = new EnabledRecipe($recipeName, $baseline);

        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->recipeName === $recipeName) {
                $this->enabledRecipes[$key] = $recipeToBeEnabled;
                return;
            }
        }

        $this->enabledRecipes[] = $recipeToBeEnabled;
    }


    public function disableRecipe(RecipeName $recipeName): void
    {
        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->recipeName === $recipeName) {
                unset($this->enabledRecipes[$key]);
                return;
            }
        }
    }


    public function configureBuild(BuildConfiguration $buildConfiguration): void
    {
       $this->buildConfiguration = $buildConfiguration;
    }
}
