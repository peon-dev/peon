<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\Process;

use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Rector\RectorBinary;
use Symfony\Component\Process\Process;

final class SymfonyProcessRectorBinary implements RectorBinary
{
    private const BINARY_EXECUTABLE = 'vendor/bin/rector'; // TODO must be dynamic, for non-standard installations


    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $command = sprintf(
            '%s %s',
            self::BINARY_EXECUTABLE,
            $command
        );

        $process = Process::fromShellCommandline($command, $directory);
        $process->run();

        return new SymfonyProcessResult($process);
    }
}
