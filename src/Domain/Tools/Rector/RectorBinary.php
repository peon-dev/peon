<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Rector;

use PHPMate\Domain\Process\ProcessFailed;
use PHPMate\Domain\Process\ProcessResult;

interface RectorBinary
{
    /**
     * @throws RectorCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult;
}
