<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\Job;

use Doctrine\DBAL\Connection;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Ui\ReadModel\Dashboard\ReadJob;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadJobById // TODO: test
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    public function provide(JobId $jobId): ReadJob
    {
        $sql = <<<SQL
SELECT 
    job.job_id, job.project_id, job.task_id, job.enabled_recipe->>'recipe_name' AS recipe_name, job.title, job.scheduled_at, job.started_at, job.succeeded_at, job.failed_at,
    job.merge_request_url,
    project.name as project_name,
    SUM(job_process_result.execution_time) as execution_time
FROM job
JOIN project ON project.project_id = job.project_id
LEFT JOIN task ON task.task_id = job.task_id
LEFT JOIN job_process_result ON job.job_id = job_process_result.job_id
WHERE job.job_id = ?
GROUP BY job.job_id, job_process_result.job_id, project.name, job.scheduled_at
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$jobId]);
        $data = $resultSet->fetchAssociative();

        return $this->hydrator->hydrateArray($data, ReadJob::class);
    }
}
