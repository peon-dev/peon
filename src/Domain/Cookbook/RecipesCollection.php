<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook;

use PHPMate\Domain\Cookbook\Value\RecipeName;

interface RecipesCollection
{
    /**
     * @return array<Recipe>
     */
    public function all(): array;

    public function hasRecipeWithName(RecipeName $recipeName): bool;

    public function get(RecipeName $recipeName): Recipe;
}
