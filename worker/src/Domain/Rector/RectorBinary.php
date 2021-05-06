<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Rector;

use PHPMate\Worker\Domain\Process\ProcessResult;

interface RectorBinary
{
    public function executeCommand(string $directory, string $command): ProcessResult;
}
