<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

final class EnableRecipeWithBaselineForProject
{
    public function __construct(
        public readonly RecipeName $recipeName,
        public readonly ProjectId $projectId,
    ) {}
}
