<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class LocalApplication
{
    public function __construct(
        public string $workingDirectory,
        public string $mainBranch,
        public string $jobBranch
    ) {}
}
