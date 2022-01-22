<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Process\Exception\ProcessFailed;
use Symfony\Component\Process\Process;

final class RunCommand
{
    /**
     * @throws ProcessFailed
     */
    public function inDirectory(string $workingDirectory, string $command, int $timeoutSeconds = 60): void
    {
        // Save process to database
        // Sanitize command
        // Run process
        // Stream output
        // Sanitize output
        // When exit, save exit code to database

        // TODO: hide :-)
        try {
            $process = Process::fromShellCommandline($command, $workingDirectory, ['SHELL_VERBOSITY' => 0], timeout: $timeoutSeconds);
            $process->mustRun(); // TODO callback to log

            return SymfonyProcessToProcessResultMapper::map($process);
        } catch (ProcessFailedException $processFailedException) {
            throw new ProcessFailed($processFailedException->getMessage(), previous: $processFailedException);
        }
    }
}
