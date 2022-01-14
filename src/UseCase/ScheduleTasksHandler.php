<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use Peon\Domain\Task\Value\TaskId;

final class ScheduleTasksHandler
{
    public function __invoke(ScheduleTasks $command): void
    {
        $sql = <<<SQL
SELECT task.task_id, task.schedule, MAX(scheduled_at) as last_schedule
FROM job
RIGHT JOIN task ON task.task_id = job.task_id
WHERE task.schedule IS NOT NULL
GROUP BY task.task_id;
SQL;

        /**
         * @var array<array{task_id: string, schedule: string, last_schedule: ?string}> $data
         */
        $data = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();

        foreach ($data as $row) {
            try {
                $cron = new CronExpression($row['schedule']);
                $now = $this->clock->now();
                $taskId = new TaskId($row['task_id']);

                // First time - never scheduled before
                if ($row['last_schedule'] === null) {
                    $this->commandBus->dispatch(
                        new RunTask($taskId)
                    );

                    continue;
                }

                $lastSchedule = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_schedule']);
                assert($lastSchedule instanceof \DateTimeImmutable);

                $nextSchedule = $cron->getNextRunDate($lastSchedule);

                if ($nextSchedule->format('Y-m-d H:i') <= $now->format('Y-m-d H:i')) {
                    $this->commandBus->dispatch(
                        new RunTask($taskId)
                    );
                }
            }
        }
    }
}
