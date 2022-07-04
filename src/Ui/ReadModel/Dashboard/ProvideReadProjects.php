<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use Doctrine\DBAL\Connection;
use Peon\Domain\GitProvider\Exception\UnknownGitProvider;
use Peon\Domain\GitProvider\Value\GitProviderName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\User\Value\UserId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadProjects
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ObjectHydrator $hydrator,
    ) {}


    /**
     * @param array<ProjectId> $projectIdentifiers
     * @return array<ReadProject>
     */
    public function provide(array $projectIdentifiers): array
    {
        $sql = <<<SQL
SELECT
       project.project_id AS "projectId",
       project.name,
       remote_git_repository_repository_uri AS "remoteGitRepositoryUri",
       count(DISTINCT task.task_id) AS "tasksCount",
       count(DISTINCT job.job_id) AS "jobsCount",
       json_array_length(project.enabled_recipes) AS "recipesCount"
FROM project
LEFT JOIN job ON job.project_id = project.project_id
LEFT JOIN task ON task.project_id = project.project_id
WHERE project.project_id IN (:projectIdentifiers)
GROUP BY project.project_id
ORDER BY project.name
SQL;

        $resultSet = $this->connection->executeQuery($sql, [
            'projectIdentifiers' => $projectIdentifiers,
        ], [
            'projectIdentifiers' => Connection::PARAM_STR_ARRAY,
        ]);

        $rows = $resultSet->fetchAllAssociative();

        foreach ($rows as $key => $row) {
            try{
                assert(is_string($row['remoteGitRepositoryUri']));
                $rows[$key]['gitProviderName'] = GitProviderName::determineFromRepositoryUri($row['remoteGitRepositoryUri']);
            } catch (UnknownGitProvider) {
                $rows[$key]['gitProviderName'] = null;
            }
        }

        return $this->hydrator->hydrateArrays($rows, ReadProject::class);
    }
}
