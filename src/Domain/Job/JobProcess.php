<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Process\ProcessResult;

#[Immutable]
class JobProcess
{
    public function __construct(
        public JobId $jobId,
        public int $counter,
        public ProcessResult $processResult
    ) {}
}
