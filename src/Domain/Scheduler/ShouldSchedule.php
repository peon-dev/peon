<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Exception;
use Lcobucci\Clock\Clock;

final class ShouldSchedule
{
    public function __construct(
        private Clock $clock,
    ) {}


    /**
     * @throws Exception
     */
    public function cronExpressionNow(CronExpression $cronExpression, DateTimeImmutable|null $lastTimeScheduledAt): bool
    {
        if ($lastTimeScheduledAt === null) {
            return true;
        }

        $nextSchedule = $cronExpression->getNextRunDate($lastTimeScheduledAt);

        return $nextSchedule <= $this->clock->now();
    }
}
