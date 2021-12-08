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
SELECT
       project.project_id, project.name,
       count(DISTINCT task.task_id) as tasks_count,
       count(DISTINCT job.job_id) as jobs_count,
       cardinality(project.enabled_recipes) as recipes_count
FROM project
LEFT JOIN job ON job.project_id = project.project_id
LEFT JOIN task ON task.project_id = project.project_id
GROUP BY project.project_id
ORDER BY project.name
SQL;

        $resultSet = $this->connection->executeQuery($sql);

        return $this->hydrator->hydrateArrays($resultSet->fetchAllAssociative(), ReadProject::class);
    }
}
