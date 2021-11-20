<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Value\ProjectId;

#[Immutable]
final class ChangeProjectRecipes
{
    /**
     * @param array<RecipeName> $recipes
     */
    public function __construct(
        public ProjectId $projectId,
        public array $recipes
    ) {}
}
