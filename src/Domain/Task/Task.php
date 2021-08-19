<?php

declare(strict_types=1);

namespace PHPMate\Domain\Task;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Project\ProjectId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
class Task
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public TaskId $taskId,
        public ProjectId $projectId,
        public string $name,
        public array $commands
    ) {}


    /**
     * @param array<string> $commands
     */
    public function changeDefinition(string $name, array $commands): void
    {
        $this->name = $name;
        $this->commands = $commands;
    }
}
