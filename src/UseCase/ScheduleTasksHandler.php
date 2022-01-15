<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Exception;
use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Packages\MessageBus\Command\CommandBus;

final class ScheduleTasksHandler
{
    public function __construct(
        private Clock $clock,
        private CommandBus $commandBus,
        private GetTaskSchedules $getTaskSchedules,
    ) {}


    /**
     * @throws Exception
     */
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
