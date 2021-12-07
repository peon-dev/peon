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
            [
                '../../../bin/console phpmate:run-recipe:unused-private-methods $PWD' // TODO: oh my god this is so hacky and ugly :-) FIX IT
            ]
        );

        $this->recipes[] = new Recipe(
            RecipeName::TYPED_PROPERTIES(),
            'Typed properties',
            file_get_contents(__DIR__ . '/CodeSnippets/typed-properties.diff'),
            7.4,
            [
                '../../../bin/console phpmate:run-recipe:typed-properties $PWD' // TODO: oh my god this is so hacky and ugly :-) FIX IT
            ]
        );

        $this->recipes[] = new Recipe(
            RecipeName::SWITCH_TO_MATCH(),
            'Switch to match',
            file_get_contents(__DIR__ . '/CodeSnippets/switch-to-match.diff'),
            8.0,
            [
                '../../../bin/console phpmate:run-recipe:switch-to-match $PWD' // TODO: oh my god this is so hacky and ugly :-) FIX IT
            ]
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


    /**
     * @return array<Recipe>
     */
    public function all(): array
    {
        return $this->recipes;
    }
}
