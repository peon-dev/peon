<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;

final class RunRecipe
{
    public function __construct(
        public readonly RecipeName $recipeName
    ) {}
}
