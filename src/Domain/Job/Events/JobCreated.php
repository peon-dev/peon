<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job\Events;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Job\JobId;

#[Immutable]
final class JobCreated
{
    public function __construct(
        public JobId $jobId
    ) {}
}
