<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Scheduler;

use Cron\CronExpression;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Domain\Scheduler\TaskSchedule;
use Peon\Domain\Task\Value\TaskId;

final class DoctrineGetTaskSchedules implements GetTaskSchedules
{
    public function __construct(
        private Connection $connection
    ) {}


    /**
     * @return array<TaskSchedule>
     */
    public function get(): array
    {
        $sql = <<<SQL
SELECT task.task_id, task.schedule, MAX(scheduled_at) as last_schedule
FROM task
LEFT JOIN job ON task.task_id = job.task_id
WHERE task.schedule IS NOT NULL
GROUP BY task.task_id;
SQL;

        $data = $this->connection->executeQuery($sql)->fetchAllAssociative();

        return array_map(static function(array $row) {
            /** @var array{task_id: string, schedule: string, last_schedule: ?string} $row */

            $lastTimeScheduledAt = $row['last_schedule'] ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_schedule']) : null;
            assert($lastTimeScheduledAt instanceof DateTimeImmutable || $lastTimeScheduledAt === null);

            return new TaskSchedule(
                new TaskId($row['task_id']),
                new CronExpression($row['schedule']),
                $lastTimeScheduledAt,
            );
        }, $data);
    }
}
