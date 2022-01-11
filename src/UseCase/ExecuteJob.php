<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Job\Value\JobId;

#[Immutable]
final class ExecuteJob
{
    public function __construct(
        public JobId $jobId
    ) {}
}
