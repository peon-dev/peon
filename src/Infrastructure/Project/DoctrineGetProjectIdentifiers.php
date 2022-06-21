<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Project;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\GetProjectIdentifiers;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\User\Value\UserId;

final class DoctrineGetProjectIdentifiers implements GetProjectIdentifiers
{
    public function __construct(
        private readonly Connection $connection
    ) {}


    /**
     * @return array<ProjectId>
     */
    public function ownedByUser(UserId $userId): array
    {
        $query = <<<SQL
SELECT project_id
FROM project
WHERE owner_user_id = :userId
SQL;

        $resultSet = $this->connection->executeQuery($query, [$userId->id]);
        $identifiers = $resultSet->fetchFirstColumn();

        return array_map(static function(string $projectId) {
            return new ProjectId($projectId);
        }, $identifiers);
    }
}
