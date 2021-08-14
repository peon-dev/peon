<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;

final class RedefineTaskHandler
{
    public function __construct(
        private TasksCollection $tasks
    ) {}


    /**
     * @param array<string> $commands
     * @throws TaskNotFound
     */
    public function handle(TaskId $taskId, string $name, array $commands): void
    {
        $task = $this->tasks->get($taskId);

        $task->changeDefinition($name, $commands);
    }
}
