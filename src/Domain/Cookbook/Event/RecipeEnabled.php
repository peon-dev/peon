<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook\Event;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

final class RecipeEnabled
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName,
    ) {}
}
