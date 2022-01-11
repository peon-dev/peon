<?php

declare(strict_types=1);

namespace Peon\Domain\Job\Event;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\ProjectId;

final class JobStatusChanged
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProjectId $projectId,
    ) {}
}
