<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\Git\GitBinary;

class ShellExecGitBinary implements GitBinary
{
    private const BINARY_EXECUTABLE = 'git';

    public function execInDirectory(WorkingDirectory $workingDirectory, string $command): void
    {
        $command = sprintf(
            'cd %s && %s %s',
            $workingDirectory->path,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
