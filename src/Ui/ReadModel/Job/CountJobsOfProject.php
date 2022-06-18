<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Job;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\Value\ProjectId;

final class CountJobsOfProject
{
    public function __construct(
        private readonly Connection $connection,
    ) {}


    public function count(ProjectId $projectId): int
    {
        $sql = <<<SQL
SELECT 
    COUNT(job.job_id) 
FROM job
WHERE job.project_id = ?
LIMIT 1
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$projectId]);
        $count = $resultSet->fetchOne();

        assert(is_int($count));

        return $count;
    }
}
