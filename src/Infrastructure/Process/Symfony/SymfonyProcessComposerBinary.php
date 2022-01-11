<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Process\Symfony;

use Peon\Domain\Tools\Composer\ComposerBinary;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Composer\Exception\ComposerCommandFailed;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessComposerBinary implements ComposerBinary
{
    /**
     * @throws ComposerCommandFailed
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
