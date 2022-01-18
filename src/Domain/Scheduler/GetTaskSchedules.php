<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

interface GetTaskSchedules
{
    /**
     * @return array<TaskJobSchedule>
     */
    public function all(): array;
}
