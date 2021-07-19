<?php

declare(strict_types=1);

namespace PHPMate\Tasks\UseCases;

use PHPMate\Tasks\TaskCanNotHaveNoScripts;
use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\TaskNotFound;
use PHPMate\Tasks\Tasks;

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

        $task->redefine($name, $scripts);

        $this->tasks->save($task);
    }
}
