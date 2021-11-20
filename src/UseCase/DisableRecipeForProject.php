<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

#[Immutable]
final class DisableRecipeForProject
{
    public function __construct(
        public RecipeName $recipeName,
        public ProjectId $projectId
    ) {}
}
