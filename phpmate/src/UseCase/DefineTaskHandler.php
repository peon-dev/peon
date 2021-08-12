<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskCanNotHaveNoScripts;
use PHPMate\Domain\Task\Tasks;

final class DefineTaskHandler
{
    public function __construct(
        private Tasks $tasks
    ) {}


    /**
     * @param array<string> $scripts
     * @throws TaskCanNotHaveNoScripts
     */
    public function handle(string $name, array $scripts): void
    {
        $taskId = $this->tasks->provideNextIdentity();

        $task = Task::define($taskId, $name, $scripts);

        $this->tasks->add($task);
    }
}
