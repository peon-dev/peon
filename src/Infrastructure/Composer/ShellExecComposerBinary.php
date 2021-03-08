<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Composer;

use PHPMate\Domain\Composer\ComposerBinary;

final class ShellExecComposerBinary implements ComposerBinary
{
    public function __construct(
        private string $directory
    ) {}


    public function exec(string $command): void
    {
        $command = sprintf('cd %s && %s', $this->directory, $command);

        shell_exec($command);
    }
}
