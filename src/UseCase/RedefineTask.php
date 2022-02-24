<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use JetBrains\PhpStorm\Immutable;
use Peon\Domain\Task\Value\TaskId;

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
        public ?CronExpression $schedule,
        public bool $mergeAutomatically,
    ) {}
}
