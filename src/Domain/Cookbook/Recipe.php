<?php

declare(strict_types=1);

namespace Peon\Domain\Cookbook;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Cookbook\Value\RecipeName;

final class Recipe
{
    public function __construct(
        public readonly RecipeName $name,
        public readonly string $title,
        public readonly ?string $exampleCodeDiff,
    ) {}
}
