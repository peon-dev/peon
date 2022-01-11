<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Composer;

use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Composer\Exception\ComposerCommandFailed;

interface ComposerBinary
{
    /**
     * @param array<string, string> $environmentVariables
     * @throws ComposerCommandFailed
     */
    public function executeCommand(string $directory, string $command, array $environmentVariables = []): ProcessResult;
}
