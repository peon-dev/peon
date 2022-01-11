<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Rector;

use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Rector\Exception\RectorCommandFailed;

interface RectorBinary
{
    /**
     * @throws RectorCommandFailed
     */
    public function executeCommand(string $directory, string $command): ProcessResult;
}
