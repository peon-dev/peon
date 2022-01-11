<?php

declare(strict_types=1);

namespace Peon\Domain\Cookbook\Event;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;

final class RecipeEnabled
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly RecipeName $recipeName,
    ) {}
}
