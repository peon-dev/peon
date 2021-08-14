<?php

declare(strict_types=1);

namespace PHPMate\Domain\Task;

interface TasksCollection
{
    public function provideNextIdentity(): TaskId;

    public function save(Task $task): void;

    /**
     * @throws TaskNotFound
     */
    public function remove(TaskId $taskId): void;

    /**
     * @throws TaskNotFound
     */
    public function get(TaskId $taskId): Task;

    /**
     * @return array<Task>
     */
    public function getAll(): array;
}
