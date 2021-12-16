<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Project\Value\EnabledRecipe;

class Project
{
    #[Immutable]
    public string $name;

    /**
     * @var array<EnabledRecipe>
     */
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public array $enabledRecipes = [];

    public function __construct(
        public ProjectId $projectId,
        public RemoteGitRepository $remoteGitRepository
    ) {
        $this->name = $this->remoteGitRepository->getProject();
    }


    public function enableRecipe(RecipeName $recipeName, string|null $baseline = null): void
    {
        $recipeToBeEnabled = new EnabledRecipe($recipeName, $baseline);

        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->recipeName->equals($recipeName)) {
                $this->enabledRecipes[$key] = $recipeToBeEnabled;
                return;
            }
        }

        $this->enabledRecipes[] = $recipeToBeEnabled;
    }


    public function disableRecipe(RecipeName $recipeName): void
    {
        foreach ($this->enabledRecipes as $key => $enabledRecipe) {
            if ($enabledRecipe->recipeName->equals($recipeName)) {
                unset($this->enabledRecipes[$key]);
                return;
            }
        }
    }
}
