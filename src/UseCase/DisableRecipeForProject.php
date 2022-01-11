<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;

#[Immutable]
final class DisableRecipeForProject
{
    public function __construct(
        public RecipeName $recipeName,
        public ProjectId $projectId
    ) {}
}
