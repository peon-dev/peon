<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Job\Value\JobId;

#[Immutable]
final class ExecuteTaskJob
{
    public function __construct(
        public JobId $jobId
    ) {}
}
