<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TasksCollection;

final class DefineTaskHandler
{
    public function __construct(
        private TasksCollection $tasks
    ) {}


    /**
     * @param array<string> $commands
     */
    public function handle(ProjectId $projectId, string $name, array $commands): void
    {
        $taskId = $this->tasks->provideNextIdentity();

        $task = new Task($taskId, $projectId, $name, $commands);

        $this->tasks->add($task);
    }
}
