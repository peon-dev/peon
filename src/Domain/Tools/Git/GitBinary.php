<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Git\Exception\GitCommandFailed;

interface GitBinary
{
    /**
     * @throws GitCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult;
}
