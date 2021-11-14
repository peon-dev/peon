<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\RecipeName;
use PHPMate\Domain\Project\ProjectId;

final class EnableRecipeForProject
{
    public function __construct(
        public RecipeName $recipeName,
        public ProjectId $projectId
    ) {}
}
