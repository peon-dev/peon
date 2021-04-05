<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;

final class ShellExecComposerBinary implements ComposerBinary
{
    private const BINARY_EXECUTABLE = 'composer';

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
