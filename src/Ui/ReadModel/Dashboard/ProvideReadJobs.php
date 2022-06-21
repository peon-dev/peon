<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadJobs
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectHydrator $hydrator,
    ) {}


    /**
     * @param array<ProjectId> $projectIdentifiers
     * @return array<ReadJob>
     */
    public function provide(array $projectIdentifiers, int $maxJobsLimit): array
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
    job.canceled_at AS "canceledAt",
    job.merge_request->>'url' AS "mergeRequestUrl",
    project.name AS "projectName",
    SUM(process.execution_time) AS "executionTime"
FROM job
JOIN project ON project.project_id = job.project_id
LEFT JOIN task ON task.task_id = job.task_id
LEFT JOIN process ON job.job_id = process.job_id
WHERE project.project_id IN (:projectIdentifiers)
GROUP BY job.job_id, process.job_id, project.name, job.scheduled_at
ORDER BY job.scheduled_at DESC
LIMIT :maxJobs
SQL;

        $resultSet = $this->connection->executeQuery($sql, [
            'projectIdentifiers' => $projectIdentifiers,
            'maxJobs' => $maxJobsLimit,
        ], [
            'projectIdentifiers' => Connection::PARAM_STR_ARRAY,
            'maxJobs' => 'integer',
        ]);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadJob::class);
    }
}
