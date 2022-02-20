<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadTasks
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadTask>
     */
    public function provide(ProjectId $projectId): array
    {
        $sql = <<<SQL
SELECT 
    task.task_id AS "taskId",
    task.name,
    task.schedule,
    task.commands,
    job.job_id AS "lastJobId", 
    job.started_at AS "lastJobStartedAt", 
    job.failed_at AS "lastJobFailedAt", 
    job.succeeded_at AS "lastJobSucceededAt",
    job.scheduled_at AS "lastJobScheduledAt",
    job.canceled_at AS "lastJobCanceledAt",
    job.merge_request->>'url' AS "lastJobMergeRequestUrl"
FROM task
LEFT JOIN job ON job.task_id = task.task_id AND job.scheduled_at = (
    SELECT MAX(latest_job.scheduled_at)
    FROM job latest_job
    WHERE latest_job.task_id = task.task_id
    GROUP BY latest_job.task_id
)
WHERE task.project_id = :projectId
ORDER BY task.name
SQL;

        $resultSet = $this->connection->executeQuery($sql, ['projectId' => $projectId->id]);
        $rows = $resultSet->fetchAllAssociative();

        return $this->hydrator->hydrateArrays($rows, ReadTask::class);
    }
}
