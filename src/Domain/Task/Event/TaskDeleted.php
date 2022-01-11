<?php

declare(strict_types=1);

namespace Peon\Domain\Task\Event;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;

final class TaskDeleted
{
    public function __construct(
        public readonly TaskId $taskId,
        public readonly ProjectId $projectId,
    ) {}
}
