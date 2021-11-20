<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook\Value;

final class RecipeName
{
    public function __construct(
        public string $name
    ) {}

    public function isEqual(RecipeName $recipeName): bool
    {
        return $this->name === $recipeName->name;
    }
}
