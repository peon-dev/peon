<?php

declare(strict_types=1);

namespace Peon\Domain\Task;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
class Task
{
    public ?CronExpression $schedule;

    /**
     * @param array<string> $commands
     */
    public function __construct(
        public TaskId $taskId,
        public ProjectId $projectId,
        public string $name,
        public array $commands,
        public bool $mergeAutomatically,
    ) {}


    /**
     * @param array<string> $commands
     */
    public function changeDefinition(
        string $name, array
        $commands,
        bool $mergeAutomatically,
    ): void
    {
        $this->name = $name;
        $this->commands = $commands;
        $this->mergeAutomatically = $mergeAutomatically;
    }


    public function changeSchedule(?CronExpression $schedule): void
    {
        $this->schedule = $schedule;
    }
}
