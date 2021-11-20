<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Task\Value\TaskId;

#[Immutable]
final class RedefineTask
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public TaskId $taskId,
        public string $name,
        public array $commands,
        public ?CronExpression $schedule
    ) {}
}
