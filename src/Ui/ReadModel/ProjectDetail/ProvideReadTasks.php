<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use PHPMate\Domain\Project\Value\ProjectId;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadTasks
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadTask>
     */
    public function provide(ProjectId $projectId): array
    {
        $sql = <<<SQL
SELECT 
	task.task_id, task.name, task.schedule, task.commands,
	job.job_id as last_job_id, 
	job.started_at as last_job_started_at, 
	job.failed_at as last_job_failed_at, 
	job.succeeded_at as last_job_succeeded_at,
	job.scheduled_at as last_job_scheduled_at
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
