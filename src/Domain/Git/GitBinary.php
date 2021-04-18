<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\Process\ProcessResult;

interface GitBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult;
}
