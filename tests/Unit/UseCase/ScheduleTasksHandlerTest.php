<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Cron\CronExpression;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Scheduler\ShouldSchedule;
use Peon\Domain\Scheduler\TaskJobSchedule;
use Peon\Domain\Task\Value\TaskId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RunTask;
use Peon\UseCase\ScheduleTasks;
use Peon\UseCase\ScheduleTasksHandler;
use PHPUnit\Framework\TestCase;

final class ScheduleTasksHandlerTest extends TestCase
{
    public function testRunTaskCommandsWillBeDispatched(): void
    {
        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(RunTask::class));

        $taskJobSchedule = new TaskJobSchedule(
            new TaskId(''),
            new CronExpression('* * * * *'),
            null,
        );
        $getTaskSchedules = $this->createMock(GetTaskSchedules::class);
        $getTaskSchedules->expects(self::once())
            ->method('all')
            ->willReturn([
                $taskJobSchedule,
                $taskJobSchedule,
            ]);

        $shouldSchedule = $this->createMock(ShouldSchedule::class);
        $shouldSchedule->expects(self::exactly(2))
            ->method('cronExpressionNow')
            ->willReturnOnConsecutiveCalls(true, false);

        $jobsCollection = new InMemoryJobsCollection();

        $handler = new ScheduleTasksHandler(
            $commandBus,
            $getTaskSchedules,
            $shouldSchedule,
            $jobsCollection,
        );

        $command = new ScheduleTasks();
        $handler->__invoke($command);
    }
}
