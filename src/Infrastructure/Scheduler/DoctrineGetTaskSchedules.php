<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Scheduler;

use Doctrine\DBAL\Connection;
use Peon\Domain\Scheduler\GetTaskSchedules;

final class DoctrineGetTaskSchedules implements GetTaskSchedules
{
    public function __construct(private Connection $connection)
    {
    }


    public function get(): array
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
        $data = $this->connection->executeQuery($sql)->fetchAllAssociative();
    }
}
