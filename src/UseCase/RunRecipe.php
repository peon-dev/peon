<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

final class RunRecipe
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName
    ) {}
}
