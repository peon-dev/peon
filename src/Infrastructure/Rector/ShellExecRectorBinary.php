<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Rector;

use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Rector\RectorBinary;

final class ShellExecRectorBinary implements RectorBinary
{
    private const BINARY_EXECUTABLE = 'vendor/bin/rector'; // TODO must be dynamic, for non-standard installations

    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $command = sprintf(
            'cd %s && %s %s',
            $directory,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
