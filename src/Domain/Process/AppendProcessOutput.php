<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

use Peon\Domain\Process\Value\ProcessId;

interface AppendProcessOutput
{
    public function toStandard(ProcessId $processId, string $output): void;

    public function toError(ProcessId $processId, string $output): void;
}
