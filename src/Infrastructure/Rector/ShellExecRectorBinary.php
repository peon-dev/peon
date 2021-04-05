<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\Rector\RectorBinary;

final class ShellExecRectorBinary implements RectorBinary
{
    private const BINARY_EXECUTABLE = 'vendor/bin/rector';

    public function execInDirectory(string $workingDirectory, string $command): void
    {
        $command = sprintf(
            'cd %s && %s %s',
            $workingDirectory,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
