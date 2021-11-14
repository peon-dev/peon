<?php

declare(strict_types=1);

namespace PHPMate\Domain\Cookbook;

final class RecipeName
{
    public function __construct(
        public string $name
    ) {}
}
