<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Process\Symfony;

use PHPMate\Domain\Tools\Composer\ComposerBinary;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessComposerBinary implements ComposerBinary
{
    /**
     * @throws \PHPMate\Domain\Tools\Composer\Exception\ComposerCommandFailed
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult
    {
        try {
            $process = Process::fromShellCommandline('composer ' . $command, $directory, $environmentVariables);
            $process->mustRun();

            return SymfonyProcessToProcessResultMapper::map($process);
        } catch (ProcessFailedException $processFailedException) {
            throw new ComposerCommandFailed($processFailedException->getMessage(), previous: $processFailedException);
        }
    }
}
