<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;

final class RedefineTask
{
    public function __construct(
        private TasksCollection $tasks
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function handle(RedefineTaskCommand $command): void
    {
        $task = $this->tasks->get($command->taskId);

        $task->changeDefinition($command->name, $command->commands);

        $this->tasks->save($task);
    }
}
