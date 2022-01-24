<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Process\Value\ProcessId;
use Peon\Domain\Process\Value\SanitizedCommand;

class Process
{
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public float|null $executionTime = null;

    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public int|null $exitCode = null;

    public function __construct(
        public readonly ProcessId $processId,
        public readonly SanitizedCommand $sanitizedCommand,
        public readonly int $timeoutSeconds,
    ) {}


    public function finish(int $exitCode, float $executionTime): void
    {
        $this->exitCode = $exitCode;
        $this->executionTime = $executionTime;
    }
}
