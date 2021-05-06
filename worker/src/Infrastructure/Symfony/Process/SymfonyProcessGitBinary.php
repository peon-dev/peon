<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Symfony\Process;

use PHPMate\Worker\Domain\Git\GitBinary;
use PHPMate\Worker\Domain\Process\ProcessResult;
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
