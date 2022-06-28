<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Job\Value\JobId;

final class RerunJob
{
    public function __construct(
        public readonly JobId $jobId,
    ) {}
}
