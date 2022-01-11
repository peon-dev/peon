<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Process\Value\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessToProcessResultMapper
{
    /**
     * @param Process<string> $process
     */
    public static function map(Process $process): ProcessResult
    {
        $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
        $executionTime = (float) $process->getLastOutputTime() - $process->getStartTime();

        return new ProcessResult(
            $process->getCommandLine(),
            (int) $process->getExitCode(),
            $output,
            $executionTime
        );
    }
}
