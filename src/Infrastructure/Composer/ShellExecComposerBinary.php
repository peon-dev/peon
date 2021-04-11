<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\FileSystem\WorkingDirectory;

final class ShellExecComposerBinary implements ComposerBinary
{
    public static string $COMPOSER_AUTH = '';

    private const BINARY_EXECUTABLE = 'composer';

    public function executeCommand(WorkingDirectory $projectDirectory, string $command): void
    {
        $command = sprintf(
            'cd %s && COMPOSER_AUTH=\'%s\' %s %s', // TODO pass COMPOSER_AUTH somehow as argument
            $projectDirectory->getAbsolutePath(),
            self::$COMPOSER_AUTH,
            self::BINARY_EXECUTABLE,
            $command
        );

        shell_exec($command);
    }
}
