<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Worker;

use Doctrine\DBAL\Connection;

final class CountScheduledJobs
{
    public function __construct(
        private readonly Connection $connection,
    ) {}


    public function count(): int
    {
        $sql = <<<SQL
SELECT 
    COUNT(job.job_id) 
FROM job
WHERE job.scheduled_at IS NOT NULL
    AND job.started_at IS NULL
    AND job.canceled_at IS NULL
LIMIT 1
SQL;

        $resultSet = $this->connection->executeQuery($sql);
        $count = $resultSet->fetchOne();

        assert(is_int($count));

        return $count;
    }
}
