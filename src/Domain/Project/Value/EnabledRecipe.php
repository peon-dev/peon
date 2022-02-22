<?php

declare(strict_types=1);

namespace Peon\Domain\Project\Value;

use Peon\Domain\Cookbook\Value\RecipeName;

final class EnabledRecipe
{
    public function __construct(
        public readonly RecipeName $recipeName,
        public readonly string|null $baselineHash,
        public readonly RecipeJobConfiguration $configuration,
    ) {}


    public static function withoutConfiguration(
        RecipeName $recipeName,
        string|null $baselineHash,
    ): self
    {
        return new self($recipeName, $baselineHash, RecipeJobConfiguration::createDefault());
    }


    public function configure(RecipeJobConfiguration $configuration): self
    {
        return new self(
            $this->recipeName,
            $this->baselineHash,
            $configuration
        );
    }
}
