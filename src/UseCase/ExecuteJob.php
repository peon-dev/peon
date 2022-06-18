<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Job\Value\JobId;

final class ExecuteJob
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly bool $mergeAutomatically,
    ) {}
}
