<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Project;

final class ProjectRecipesFormData
{
    /**
     * @var array<string>
     */
    public array $recipes = [];


    /**
     * @return array<RecipeName>
     */
    public function getRecipeNames(): array
    {
        $names = [];

        foreach ($this->recipes as $recipeName) {
            $names[] = RecipeName::fromString($recipeName);
        }

        return $names;
    }


    public static function fromProject(Project $project): self
    {
        $self = new self();
        $self->recipes = array_map(static fn(RecipeName $recipeName) => $recipeName->toString(), $project->enabledRecipes);

        return $self;
    }
}
