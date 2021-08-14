<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Symfony\Process;

use PHPMate\Domain\Tools\Composer\ComposerBinary;
use PHPMate\Domain\Process\ProcessResult;
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
