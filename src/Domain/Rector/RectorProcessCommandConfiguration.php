<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class RectorProcessCommandConfiguration
{
    public function __construct(
        public ?string $autoloadFile = null,
        public ?string $projectDirectory = null,
        public ?string $config = null,
    ) {}
}
