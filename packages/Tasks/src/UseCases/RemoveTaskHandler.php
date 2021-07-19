<?php

declare(strict_types=1);

namespace PHPMate\Tasks\UseCases;

use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\TaskNotFound;

final class RemoveTaskHandler
{
    /**
     * @throws TaskNotFound
     */
    public function handle(TaskId $taskId): void
    {
    }
}
