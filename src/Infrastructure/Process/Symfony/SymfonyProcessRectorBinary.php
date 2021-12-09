<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Process\Symfony;

use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessRectorBinary implements RectorBinary
{
    private const BINARY_EXECUTABLE = __DIR__ . '/../../../../vendor-bin/rector/vendor/rector/rector/bin/rector'; // TODO must be dynamic, for non-standard installations


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

        // 60 minutes timeout should be enough, ... hopefully ...
        $timeout = 60 * 60;

        try {
            $process = Process::fromShellCommandline($command, $directory, ['SHELL_VERBOSITY' => 0], timeout: $timeout);
            $process->mustRun();

            return SymfonyProcessToProcessResultMapper::map($process);
        } catch (ProcessFailedException $processFailedException) {
            throw new RectorCommandFailed($processFailedException->getMessage(), previous: $processFailedException);
        }
    }
}
