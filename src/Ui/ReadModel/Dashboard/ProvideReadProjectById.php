<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Value\ProjectId;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadProjectById // TODO: test
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function provide(ProjectId $projectId): ReadProject
    {
        $sql = <<<SQL
SELECT
       project.project_id, project.name,
       count(DISTINCT task.task_id) as tasks_count,
       count(DISTINCT job.job_id) as jobs_count,
       json_array_length(project.enabled_recipes) as recipes_count
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
