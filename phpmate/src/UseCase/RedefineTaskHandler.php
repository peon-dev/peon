<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\TaskCanNotHaveNoScripts;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\Tasks;

final class RedefineTaskHandler
{
    public function __construct(
        private Tasks $tasks
    ) {}


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     * @throws TaskNotFound
     */
    public function handle(TaskId $taskId, string $name, array $scripts): void
    {
        $task = $this->tasks->get($taskId);

        $task->changeDefinition($name, $scripts);
    }
}
