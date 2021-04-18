<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Process\ProcessResult;

class ShellExecGitBinary implements GitBinary
{
    private const BINARY_EXECUTABLE = 'git';

    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $command = sprintf(
            'cd %s && %s %s',
            $directory,
            self::BINARY_EXECUTABLE,
            $command
        );

        return trim((string) shell_exec($command));
    }
}
