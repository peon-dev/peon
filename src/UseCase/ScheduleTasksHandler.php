<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Exception;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ScheduleTasksHandler implements CommandHandlerInterface
{
    public function __construct(
        private CommandBus $commandBus,
        private GetTaskSchedules $getTaskSchedules,
        private ShouldSchedule $shouldSchedule,
    ) {}


    /**
     * @throws Exception
     */
    public function __invoke(ScheduleTasks $command): void
    {
        $schedules = $this->getTaskSchedules->all();

        foreach ($schedules as $schedule) {
            if ($this->shouldSchedule->cronExpressionNow($schedule->cronExpression, $schedule->lastTimeScheduledAt)) {
                $this->commandBus->dispatch(
                    new RunTask($schedule->taskId)
                );
            }
        }
    }
}
