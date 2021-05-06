<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Git;

use PHPMate\Worker\Domain\Process\ProcessResult;

interface GitBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult;
}
