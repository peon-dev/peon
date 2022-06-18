<?php

declare(strict_types=1);

namespace Peon\UseCase;

use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Task\Value\TaskId;

final class RunTask
{
    public function __construct(
        public readonly TaskId $taskId,
        public readonly JobId $jobId,
    ) {}
}
