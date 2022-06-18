<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use Peon\Domain\Task\Value\TaskId;

final class RedefineTask
{
    /**
     * @param array<string> $commands
     */
    public function __construct(
        public readonly TaskId $taskId,
        public readonly string $name,
        public readonly array $commands,
        public readonly ?CronExpression $schedule,
        public readonly bool $mergeAutomatically,
    ) {}
}
