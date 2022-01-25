<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Process\Value\ProcessId;

interface LogProcessOutput
{
    public function append(ProcessId $processId, string $output): void;
}
