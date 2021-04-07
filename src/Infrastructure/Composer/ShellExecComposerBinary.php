<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\FileSystem\WorkingDirectory;

final class ShellExecComposerBinary implements ComposerBinary
{
    private const BINARY_EXECUTABLE = 'composer';

    public function execInWorkingDirectory(WorkingDirectory $workingDirectory, string $command): void
    {
        $command = sprintf(
            'cd %s && %s %s',
            $workingDirectory->getAbsolutePath(),
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
