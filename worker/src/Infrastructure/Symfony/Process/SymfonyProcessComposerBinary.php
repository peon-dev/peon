<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Symfony\Process;

use PHPMate\Worker\Domain\Composer\ComposerBinary;
use PHPMate\Worker\Domain\Process\ProcessResult;
use Symfony\Component\Process\Process;

final class SymfonyProcessComposerBinary implements ComposerBinary
{
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult
    {
        $process = Process::fromShellCommandline('composer ' . $command, $directory, $environmentVariables);
        $process->run();

        return SymfonyProcessToProcessResultMapper::map($process);
    }
}
