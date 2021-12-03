<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Cookbook;

use PHPMate\Domain\Cookbook\Recipe;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Cookbook\RecipesCollection;
use function Safe\file_get_contents;

final class StaticRecipesCollection implements RecipesCollection
{
    /**
     * @var array<Recipe>
     */
    private array $recipes = [];


    public function __construct()
    {
        $this->recipes[] = new Recipe(
            RecipeName::UNUSED_PRIVATE_METHODS(),
            'Unused private methods',
            file_get_contents(__DIR__ . '/CodeSnippets/unused-private-methods.diff'),
            null,
            ["echo 'Dummy command 1'"]
        );

        $this->recipes[] = new Recipe(
            RecipeName::TYPED_PROPERTIES(),
            'Typed properties',
            file_get_contents(__DIR__ . '/CodeSnippets/typed-properties.diff'),
            7.4,
            ["echo 'Dummy command 2'"]
        );
    }


    public function hasRecipeWithName(RecipeName $recipeName): bool
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->name->equals($recipeName)) {
                return true;
            }
        }

        return false;
    }


    public function get(RecipeName $recipeName): Recipe
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->name->equals($recipeName)) {
                return $recipe;
            }
        }

        throw new \RuntimeException('Should not happen');
    }
}
