<?php

declare(strict_types=1);

namespace Peon\Domain\Cookbook;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Cookbook\Value\RecipeName;

#[Immutable]
final class Recipe
{
    public function __construct(
        public RecipeName $name,
        public string $title,
        public ?string $exampleCodeDiff,
    ) {}
}
