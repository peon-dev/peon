<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Process\ProcessResult;

final class ShellExecComposerBinary implements ComposerBinary
{
    private const BINARY_EXECUTABLE = 'composer';

    /**
     * @param array<string, string> $environmentVariables
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult
    {
        $commandEnvironmentVariablesString = '';

        foreach ($environmentVariables as $variableName => $variableValue) {
            $commandEnvironmentVariablesString .= sprintf('%s=\'%s\' ', $variableName, $variableValue);
        }

        $command = sprintf(
            'cd %s && %s%s %s',
            $directory,
            $commandEnvironmentVariablesString,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
