<?php

declare(strict_types=1);

namespace PHPMate\Domain\Process;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Process\Value\ProcessResult;

#[Immutable]
class JobProcess
{
    public function __construct(
        public Job $job,
        public int $order,
        public ProcessResult $result
    ) {}
}
