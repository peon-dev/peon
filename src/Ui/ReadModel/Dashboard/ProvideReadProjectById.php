<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadProjectById // TODO: test
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectHydrator $hydrator,
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function provide(ProjectId $projectId): ReadProject
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
WHERE project.project_id = ?
GROUP BY project.project_id
ORDER BY project.name
SQL;

        $resultSet = $this->connection->executeQuery($sql, [$projectId]);
        $data = $resultSet->fetchAssociative();

        if ($data === false) {
            throw new ProjectNotFound();
        }

        return $this->hydrator->hydrateArray($data, ReadProject::class);
    }
}
