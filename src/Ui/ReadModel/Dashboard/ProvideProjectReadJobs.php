<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideProjectReadJobs
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadJob>
     */
    public function provide(ProjectId $projectId, int $maxJobsLimit): array
    {
        $sql = <<<SQL
SELECT 
    job.job_id, job.project_id, job.task_id, job.enabled_recipe->>'recipe_name' AS recipe_name, job.title, job.scheduled_at, job.started_at, job.succeeded_at, job.failed_at,
    job.merge_request_url, project.name as project_name,
    SUM(job_process_result.execution_time) as execution_time
FROM job
JOIN project ON project.project_id = job.project_id
LEFT JOIN task ON task.task_id = job.task_id
LEFT JOIN job_process_result ON job.job_id = job_process_result.job_id
WHERE job.project_id = ?
GROUP BY job.job_id, job_process_result.job_id, project.name, job.scheduled_at
ORDER BY job.scheduled_at DESC
LIMIT ?
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$projectId->id, $maxJobsLimit], ['string', 'integer']);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadJob::class);
    }
}
