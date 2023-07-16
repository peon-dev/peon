<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Ui\ReadModel\ProjectDetail\ReadProjectDetail;

final class ConfigureRecipeFormData
{
    public bool $mergeAutomatically;
    public string $afterScript;


    public static function fromReadProjectDetail(ReadProjectDetail $project, RecipeName $recipeName): self
    {
        $configuration = $project->getRecipeConfiguration($recipeName);
        $data = new self();
        $data->mergeAutomatically = $configuration?->mergeAutomatically ?? RecipeJobConfiguration::DEFAULT_MERGE_AUTOMATICALLY_VALUE;
        $data->afterScript = $configuration?->afterScript ?? RecipeJobConfiguration::DEFAULT_AFTER_SCRIPT_VALUE;

        return $data;
    }
}
