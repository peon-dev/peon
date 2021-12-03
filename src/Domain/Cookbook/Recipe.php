<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Cookbook\Value\RecipeName;

#[Immutable]
final class Recipe
{
    public function __construct(
        public RecipeName $name,
        public string $title,
        public ?string $exampleCodeDiff,
        public ?float $minPhpVersionRequirement,
        public array $commands,
    ) {}

    // TODO: we will need required tools
    // TODO: we will need commands
}
