<?php

declare(strict_types=1);

namespace PHPMate\Domain\Task;

interface Tasks
{
    public function provideNextIdentity(): TaskId;

    public function add(Task $task): void;

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
