<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Process\Symfony;

use PHPMate\Domain\Tools\Git\GitBinary;
use PHPMate\Domain\Process\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessGitBinary implements GitBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $process = Process::fromShellCommandline('git ' . $command, $directory);
        $process->run();

        return SymfonyProcessToProcessResultMapper::map($process);
    }
}
