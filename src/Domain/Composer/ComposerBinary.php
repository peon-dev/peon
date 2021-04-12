<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface ComposerBinary
{
    /**
     * @param array<string, string> $environmentVariables
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): void;
}
