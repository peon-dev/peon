<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Composer;

use PHPMate\Worker\Domain\Process\ProcessResult;

interface ComposerBinary
{
    /**
     * @param array<string, string> $environmentVariables
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult;
}
