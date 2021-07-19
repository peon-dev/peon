<?php

declare(strict_types=1);

namespace PHPMate\Tasks\UseCases;

use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\TaskNotFound;
use PHPMate\Tasks\Tasks;

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
