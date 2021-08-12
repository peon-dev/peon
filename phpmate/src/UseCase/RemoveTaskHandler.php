<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\Tasks;

final class RemoveTaskHandler
{
    public function __construct(
        private Tasks $tasks
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function handle(TaskId $taskId): void
    {
        $this->tasks->remove($taskId);
    }
}
