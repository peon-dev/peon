<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadProjects
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
    ) {}


    /**
     * @return array<ReadProject>
     */
    public function provide(): array
    {
        $sql = <<<SQL
SELECT
       project.project_id AS "projectId",
       project.name,
       count(DISTINCT task.task_id) AS "tasksCount",
       count(DISTINCT job.job_id) AS "jobsCount",
       json_array_length(project.enabled_recipes) AS "recipesCount"
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
