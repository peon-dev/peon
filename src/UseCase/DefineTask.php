<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;

#[Immutable]
final class DefineTask
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public TaskId $taskId,
        public ProjectId $projectId,
        public string $name,
        public array $commands,
        public ?CronExpression $schedule
    ) {}
}
