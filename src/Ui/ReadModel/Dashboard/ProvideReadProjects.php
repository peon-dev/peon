<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadProjects
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadProject>
     */
    public function provide(): array
    {
        $sql = <<<SQL

SQL;

        return [];

        $resultSet = $this->connection->executeQuery($sql, [$jobsCount], ['integer']);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadProject::class);
    }
}
