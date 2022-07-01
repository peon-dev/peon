<?php

declare(strict_types=1);

namespace Peon\Domain\Job\Event;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Value\ProcessId;

final class JobProcessFinished
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProcessId $processId,
        public readonly bool $succeed,
        public readonly int $executionTime,
    ) {}
}
