<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\InMemory;

use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;

final class InMemoryTasksCollection implements TasksCollection
{
    /**
     * @var array<string, Task>
     */
    private array $tasks = [];


    public function provideNextIdentity(): TaskId
    {
        return new TaskId((string) count($this->tasks));
    }


    public function save(Task $task): void
    {
        $this->tasks[$task->taskId->id] = $task;
    }


    /**
     * @throws TaskNotFound
     */
    public function remove(TaskId $taskId): void
    {
        if (isset($this->tasks[$taskId->id]) === false) {
            throw new TaskNotFound();
        }

        unset($this->tasks[$taskId->id]);
    }


    /**
     * @throws TaskNotFound
     */
    public function get(TaskId $taskId): Task
    {
        return $this->tasks[$taskId->id] ?? throw new TaskNotFound();
    }


    /**
     * @return array<string, Task>
     */
    public function getAll(): array
    {
        return $this->tasks;
    }
}
