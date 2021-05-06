<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Rector;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class RectorProcessCommandConfiguration
{
    public function __construct(
        public ?string $autoloadFile = null,
        public ?string $workingDirectory = null,
        public ?string $config = null,
    ) {}
}
