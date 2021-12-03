<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadJobs
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadJob>
     */
    public function provide(int $maxJobsLimit): array
    {
        $sql = <<<SQL
SELECT 
    job.job_id, job.project_id, job.task_id, job.title, job.started_at, job.succeeded_at, job.failed_at, 
    project.name as project_name,
    SUM(job_process_result.execution_time) as execution_time
FROM job
JOIN project ON project.project_id = job.project_id
JOIN task ON task.task_id = job.task_id
LEFT JOIN job_process_result ON job.job_id = job_process_result.job_id
GROUP BY job.job_id, job_process_result.job_id, project.name, job.scheduled_at
ORDER BY job.scheduled_at DESC
LIMIT ?
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$maxJobsLimit], ['integer']);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadJob::class);
    }
}
