<?php

declare(strict_types=1);

namespace PHPMate\Tasks\UseCases;

use PHPMate\Tasks\Task;
use PHPMate\Tasks\Tasks;

final class DefineTaskHandler
{
    public function __construct(
        private Tasks $tasks
    ) {}


    /**
     * @param array<string> $scripts
     */
    public function handle(string $name, array $scripts): void
    {
        $taskId = $this->tasks->provideNextIdentity();

        $task = new Task($taskId, $name, $scripts);

        $this->tasks->save($task);
    }
}
