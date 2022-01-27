<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Process\RunProcess;
use Peon\Domain\Process\Value\ProcessOutput;
use Peon\Domain\Process\Value\ProcessResult;

final class SymfonyProcessRunProcess implements RunProcess
{
    public function inDirectory(
        string $workingDirectory,
        string $command,
        int $timeoutSeconds,
    ): ProcessResult
    {
        /**
         * $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
        $executionTime = (float) $process->getLastOutputTime() - $process->getStartTime();

        return new ProcessResult(
        $process->getCommandLine(),
        (int) $process->getExitCode(),
        $output,
        $executionTime
        );
         */

        return new ProcessResult(1, 20, new ProcessOutput());
    }
}
