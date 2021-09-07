<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Process\Symfony;

use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Domain\Tools\Rector\RectorCommandFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessRectorBinary implements RectorBinary
{
    private const BINARY_EXECUTABLE = 'vendor/bin/rector'; // TODO must be dynamic, for non-standard installations


    /**
     * @throws RectorCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $command = sprintf(
            '%s %s',
            self::BINARY_EXECUTABLE,
            $command
        );

        // 20 minutes should be enough, ... hopefully ...
        $timeout = 60 * 20;

        try {
            $process = Process::fromShellCommandline($command, $directory, timeout: $timeout);
            $process->mustRun();

            return SymfonyProcessToProcessResultMapper::map($process);
        } catch (ProcessFailedException $processFailedException) {
            throw new RectorCommandFailed($processFailedException->getMessage(), previous: $processFailedException);
        }
    }
}
