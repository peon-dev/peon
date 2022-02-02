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
    job.job_id AS "jobId",
    job.project_id AS "projectId",
    job.task_id AS "taskId",
    job.enabled_recipe->>'recipe_name' AS "recipeName",
    job.title,
    job.scheduled_at AS "scheduledAt",
    job.started_at AS "startedAt",
    job.succeeded_at AS "succeededAt",
    job.failed_at AS "failedAt",
    job.merge_request->>'url' AS "mergeRequestUrl",
    project.name AS "projectName",
    SUM(process.execution_time) AS "executionTime"
FROM job
JOIN project ON project.project_id = job.project_id
LEFT JOIN task ON task.task_id = job.task_id
LEFT JOIN process ON job.job_id = process.job_id
WHERE job.project_id = ?
GROUP BY job.job_id, process.job_id, project.name, job.scheduled_at
ORDER BY job.scheduled_at DESC
LIMIT ?
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$projectId->id, $maxJobsLimit], ['string', 'integer']);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadJob::class);
    }
}
