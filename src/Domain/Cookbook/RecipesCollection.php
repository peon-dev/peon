<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook;

interface RecipesCollection
{
    public function hasRecipeWithName(RecipeName $recipeName): bool;
}
