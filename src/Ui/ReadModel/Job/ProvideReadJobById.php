<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Job;

use Doctrine\DBAL\Connection;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Value\JobId;
use Peon\Ui\ReadModel\Dashboard\ReadJob;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadJobById // TODO: test
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
    ) {}


    /**
     * @throws JobNotFound
     */
    public function provide(JobId $jobId): ReadJob
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
    SUM(job.execution_time) AS "executionTime"
FROM job
JOIN project ON project.project_id = job.project_id
LEFT JOIN task ON task.task_id = job.task_id
LEFT JOIN job ON job.job_id = job.job_id
WHERE job.job_id = ?
GROUP BY job.job_id, job.job_id, project.name, job.scheduled_at
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$jobId]);
        $data = $resultSet->fetchAssociative();

        if ($data === false) {
            throw new JobNotFound();
        }

        return $this->hydrator->hydrateArray($data, ReadJob::class);
    }
}
