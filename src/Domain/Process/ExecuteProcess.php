<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Process\Value\ProcessResult;

interface ExecuteProcess
{
    public function inDirectory(
        string $workingDirectory,
        Process $process,
        AppendProcessOutput $appendProcessOutput,
    ): ProcessResult;
}
