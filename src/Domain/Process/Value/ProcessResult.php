<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Value;

class ProcessResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly float $executionTime,
    ) {}
}
