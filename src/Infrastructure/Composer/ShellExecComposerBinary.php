<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;

final class ShellExecComposerBinary implements ComposerBinary
{
    public static string $COMPOSER_AUTH = '';

    private const BINARY_EXECUTABLE = 'composer';

    public function executeCommand(string $directory, string $command): void
    {
        $command = sprintf(
            'cd %s && COMPOSER_AUTH=\'%s\' %s %s', // TODO pass COMPOSER_AUTH somehow as argument
            $directory,
            self::$COMPOSER_AUTH,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
