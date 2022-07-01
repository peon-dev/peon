<?php

declare(strict_types=1);

namespace Peon\Domain\Job\Event;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Value\ProcessId;

final class JobProcessStarted
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProcessId $processId,
        public readonly string $command,
    ) {}
}
