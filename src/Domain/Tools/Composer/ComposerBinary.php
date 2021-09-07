<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Composer;

use PHPMate\Domain\Process\ProcessResult;

interface ComposerBinary
{
    /**
     * @param array<string, string> $environmentVariables
     * @throws ComposerCommandFailed
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult;
}
