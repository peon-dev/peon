<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Process\Value\ProcessId;

interface ProcessesCollection
{
    public function nextIdentity(): ProcessId;

    public function save(Process $process): void;
}
