<?php

declare(strict_types=1);

namespace Peon\Domain\Task\Event;

use Peon\Domain\Task\Value\TaskId;

final class TaskChanged
{
    public function __construct(
        public readonly TaskId $taskId,
    ) {}
}
