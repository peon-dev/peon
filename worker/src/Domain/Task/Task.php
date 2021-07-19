<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Task;

final class Task
{
    /**
     * @param array<string> $scripts
     */
    public function __construct(
        private TaskId $taskId,
        private string $name,
        private array $scripts
    ) {}

    // function update($name, $scripts)
}
