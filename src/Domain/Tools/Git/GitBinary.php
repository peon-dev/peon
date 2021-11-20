<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Git;

use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Git\Exception\GitCommandFailed;

interface GitBinary
{
    /**
     * @throws GitCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult;
}
