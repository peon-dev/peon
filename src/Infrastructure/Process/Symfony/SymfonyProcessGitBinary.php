<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Process\Symfony;

use PHPMate\Domain\Tools\Git\GitBinary;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessGitBinary implements GitBinary
{
    /**
     * @throws GitCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult
    {
        try {
            $process = Process::fromShellCommandline('git ' . $command, $directory);
            $process->mustRun();

            return SymfonyProcessToProcessResultMapper::map($process);
        } catch (ProcessFailedException $processFailedException) {
            throw new GitCommandFailed($processFailedException->getMessage(), previous: $processFailedException);
        }
    }
}
