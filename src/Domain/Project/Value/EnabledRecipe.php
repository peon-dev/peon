<?php

declare(strict_types=1);

namespace Peon\Domain\Project\Value;

use Peon\Domain\Cookbook\Value\RecipeName;

final class EnabledRecipe
{
    public function __construct(
        public readonly RecipeName $recipeName,
        public readonly string|null $baselineHash,
    ) {}
}
