<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\RecipeAlreadyEnabled;
use PHPMate\Domain\Cookbook\RecipeName;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;

class Project
{
    #[Immutable]
    public string $name;

    /**
     * @var array<RecipeName>
     */
    private array $enabledRecipes = [];

    // @TODO: assert can connect to repository
    public function __construct(
        public ProjectId $projectId,
        public RemoteGitRepository $remoteGitRepository
    ) {
        $this->name = $this->remoteGitRepository->getProject();
    }


    /**
     * @throws RecipeAlreadyEnabled
     */
    public function enableRecipe(RecipeName $recipe): void
    {
        foreach ($this->enabledRecipes as $enabledRecipe) {
            if ($enabledRecipe->isEqual($recipe)) {
                throw new RecipeAlreadyEnabled();
            }
        }

        $this->enabledRecipes[] = $recipe;
    }
}
