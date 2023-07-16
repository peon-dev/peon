<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Value\RecipeJobConfiguration;

final class ConfigureRecipeForProject
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName,
        public readonly RecipeJobConfiguration $configuration,
    ) {}
}
