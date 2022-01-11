<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Job\Job;
use Peon\Domain\Process\Value\ProcessResult;

#[Immutable]
class JobProcessResult
{
    public function __construct(
        public Job $job,
        public int $order,
        public ProcessResult $result
    ) {}
}
