<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Cookbook;

use Peon\Domain\Cookbook\Recipe;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Cookbook\RecipesCollection;
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
            RecipeName::UNUSED_PRIVATE_METHODS,
            'Unused private methods',
            file_get_contents(__DIR__ . '/CodeSnippets/unused-private-methods.diff'), // TODO: delete, we do not really need this in domain
        );

        $this->recipes[] = new Recipe(
            RecipeName::TYPED_PROPERTIES,
            'Typed properties',
            file_get_contents(__DIR__ . '/CodeSnippets/typed-properties.diff'), // TODO: delete, we do not really need this in domain
        );

        $this->recipes[] = new Recipe(
            RecipeName::SWITCH_TO_MATCH,
            'Switch to match',
            file_get_contents(__DIR__ . '/CodeSnippets/switch-to-match.diff'), // TODO: delete, we do not really need this in domain
        );
    }


    public function hasRecipeWithName(RecipeName $recipeName): bool
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->name === $recipeName) {
                return true;
            }
        }

        return false;
    }


    public function get(RecipeName $recipeName): Recipe
    {
        foreach ($this->recipes as $recipe) {
            if ($recipe->name === $recipeName) {
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
