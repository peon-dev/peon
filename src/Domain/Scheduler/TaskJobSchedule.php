<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Peon\Domain\Task\Value\TaskId;

final class TaskJobSchedule
{
    public function __construct(
        public readonly TaskId $taskId,
        public readonly CronExpression $cronExpression,
        public readonly DateTimeImmutable|null $lastTimeScheduledAt,
    ) {}
}
