<?php

declare(strict_types=1);

namespace Peon\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\PhpApplication\Value\PhpApplicationBuildConfiguration;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Domain\User\Value\UserId;

class Project
{
    public readonly string $name;

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public null|ApplicationLanguage $language = null;

    /**
     * @var array<EnabledRecipe>
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $enabledRecipes = [];

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public PhpApplicationBuildConfiguration $buildConfiguration;

    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RemoteGitRepository $remoteGitRepository,
        public readonly UserId $ownerUserId,
    ) {
        $this->name = $this->remoteGitRepository->getProject();
        $this->buildConfiguration = PhpApplicationBuildConfiguration::createDefault();
    }


    /**
     * @throws RecipeNotEnabled
     */
    public function getEnabledRecipe(RecipeName $recipeName): EnabledRecipe
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($enabledRecipe->recipeName === $recipeName) {
                return $enabledRecipe;
            }
        }

        throw new RecipeNotEnabled();
    }


    /**
     * @throws RecipeNotEnabled
     */
    public function configureRecipe(RecipeName $recipeName, RecipeJobConfiguration $configuration): void
    {
        $recipe = $this->getEnabledRecipe($recipeName);

        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->recipeName === $recipeName) {
                $this->enabledRecipes[$key] = $recipe->configure($configuration);
                return;
            }
        }
    }


    public function enableRecipe(RecipeName $recipeName, string|null $baseline = null): void
    {
        $recipeToBeEnabled = EnabledRecipe::withoutConfiguration($recipeName, $baseline);

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


    public function configureBuild(PhpApplicationBuildConfiguration $buildConfiguration): void
    {
       $this->buildConfiguration = $buildConfiguration;
    }


    public function updateLanguage(ApplicationLanguage $language): void
    {
        $this->language = $language;
    }
}
