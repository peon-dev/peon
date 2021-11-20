<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Rector;

use PHPMate\Domain\Process\Exception\ProcessFailed;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed;

interface RectorBinary
{
    /**
     * @throws RectorCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult;
}
