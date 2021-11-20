<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process\Value;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ProcessResult
{
    public function __construct(
        public string $command,
        public int $exitCode,
        public string $output,
        public float $executionTime
    ) { }
}
