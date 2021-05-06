<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
final class ProcessResult
{
    public function __construct(
        public string $command,
        public int $exitCode,
        public string $output
    ) { }
}
