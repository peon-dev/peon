<?php

declare(strict_types=1);

namespace Peon\Domain\Task;

use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\Value\TaskId;

interface TasksCollection
{
    public function nextIdentity(): TaskId;

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
    public function all(): array;
}
