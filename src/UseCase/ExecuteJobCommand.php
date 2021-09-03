<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Job\JobId;

#[Immutable]
final class ExecuteJobCommand
{
    public function __construct(
        public JobId $jobId
    ) {}
}
