<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Process;

use Doctrine\DBAL\Connection;
use Peon\Domain\Job\Value\JobId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadProcessesByJobId
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadProcess>
     */
    public function provide(JobId $jobId): array
    {
        $sql = <<<SQL
SELECT
    process_id AS "processId",
    job_id AS "jobId",
    command,
    timeout_seconds AS "timeoutSeconds",
    execution_time AS "executionTime",
    exit_code AS "exitCode",
    output
FROM process
WHERE job_id = :jobId
ORDER BY sequence
SQL;

        $resultSet = $this->connection->executeQuery($sql, ['jobId' => $jobId->id]);
        $rows = $resultSet->fetchAllAssociative();

        return $this->hydrator->hydrateArrays($rows, ReadProcess::class);
    }
}
