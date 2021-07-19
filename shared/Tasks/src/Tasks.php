<?php

declare(strict_types=1);

namespace PHPMate\Tasks;

interface Tasks
{
    public function provideNextIdentity(): TaskId;

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
