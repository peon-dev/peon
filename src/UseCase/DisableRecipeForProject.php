<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

final class DisableRecipeForProject
{
    public function __construct(
        public RecipeName $recipeName,
        public ProjectId $projectId
    ) {}
}
