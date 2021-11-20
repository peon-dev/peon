<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Cookbook;

use PHPMate\Domain\Cookbook\Recipe;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Cookbook\RecipesCollection;

final class StaticRecipesCollection implements RecipesCollection
{
    /**
     * @var array<Recipe>
     */
    private array $recipes = [];


    public function __construct()
    {
    }


    public function hasRecipeWithName(\PHPMate\Domain\Cookbook\Value\RecipeName $recipeName): bool
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->name->isEqual($recipeName)) {
                return true;
            }
        }

        return false;
    }
}
