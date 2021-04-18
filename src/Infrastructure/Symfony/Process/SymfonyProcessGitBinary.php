<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\Process;

use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Process\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessGitBinary implements GitBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult
    {
        $process = Process::fromShellCommandline('git ' . $command, $directory);
        $process->run();

        return new SymfonyProcessResult($process);
    }
}
