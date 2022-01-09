<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job\Event;

use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Project\Value\ProjectId;

final class JobStatusChanged
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProjectId $projectId,
    ) {}
}
