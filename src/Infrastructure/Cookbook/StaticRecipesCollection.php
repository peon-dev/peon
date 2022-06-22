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

        $this->recipes[] = new Recipe(
            RecipeName::OBJECT_MAGIC_CLASS_CONSTANT,
            'Magic ::class constant for objects',
            file_get_contents(__DIR__ . '/CodeSnippets/getclass-to-object-constant.diff'), // TODO: delete, we do not really need this in domain
        );

        $this->recipes[] = new Recipe(
            RecipeName::CONSTRUCTOR_PROPERTY_PROMOTION,
            'Constructor property promotion',
            file_get_contents(__DIR__ . '/CodeSnippets/constructor-property-promotion.diff'), // TODO: delete, we do not really need this in domain
        );

        $this->recipes[] = new Recipe(
            RecipeName::USELESS_VARIABLE_IN_CATCH,
            'Useless variable in catch',
            file_get_contents(__DIR__ . '/CodeSnippets/useless-variable-in-catch.diff'), // TODO: delete, we do not really need this in domain
        );

        $this->recipes[] = new Recipe(
            RecipeName::VOID_RETURN,
            'Void return type',
            file_get_contents(__DIR__ . '/CodeSnippets/void-return.diff'), // TODO: delete, we do not really need this in domain
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
