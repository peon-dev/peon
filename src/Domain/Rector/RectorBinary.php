<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use PHPMate\Domain\Process\ProcessResult;

interface RectorBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult;
}
