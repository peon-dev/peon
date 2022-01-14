<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Packages\MessageBus\Command\CommandBus;

final class ScheduleTasksHandler
{
    public function __construct(
        private Clock $clock,
        private CommandBus $commandBus,
        private GetTaskSchedules $getTaskSchedules,
    ) {}


    public function __invoke(ScheduleTasks $command): void
    {
        $schedules = $this->getTaskSchedules->get();
        $now = $this->clock->now();

        foreach ($schedules as $schedule) {
            if ($schedule->lastTimeScheduledAt !== null) {
                $nextSchedule = $schedule->schedule->getNextRunDate($schedule->lastTimeScheduledAt);

                if ($nextSchedule > $now) {
                    continue;
                }
            }

            $this->commandBus->dispatch(
                new RunTask($schedule->taskId)
            );
        }
    }
}
