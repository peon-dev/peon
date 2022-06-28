<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Worker;

use Doctrine\DBAL\Connection;
use Lcobucci\Clock\Clock;
use UXF\Hydrator\ObjectHydrator;

final class ProvideActiveReadWorkers
{
    private const ACTIVE_INTERVAL = '-1 minute';


    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectHydrator $hydrator,
        private readonly Clock $clock,
    ) {}


    /**
     * @return array<ReadWorker>
     */
    public function provide(): array
    {
        $sql = <<<SQL
SELECT 
    worker_status.worker_id as "workerId",
    worker_status.last_seen_at as "lastSeenAt"
FROM worker_status
WHERE worker_status.last_seen_at >= :minLastSeen
ORDER BY worker_status.worker_id
SQL;

        $resultSet = $this->connection->executeQuery($sql, [
            'minLastSeen' => $this->clock->now()->modify(self::ACTIVE_INTERVAL)->format('Y-m-d H:i:s'),
        ]);
        $rows = $resultSet->fetchAllAssociative();

        return $this->hydrator->hydrateArrays($rows, ReadWorker::class);
    }
}
