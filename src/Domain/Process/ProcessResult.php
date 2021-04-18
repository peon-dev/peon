<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $output,
        public string $errorOutput
    ) {}
}
