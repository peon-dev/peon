<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Process\Value\ProcessResult;

#[Immutable]
class JobProcessResult
{
    public function __construct(
        public Job $job,
        public int $order,
        public ProcessResult $result
    ) {}
}
