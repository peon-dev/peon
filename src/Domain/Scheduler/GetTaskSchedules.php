<?php

declare(strict_types=1);

namespace Peon\Domain\Scheduler;

interface GetTaskSchedules
{
    /**
     * @return array<TaskSchedule>
     */
    public function get(): array;
}
