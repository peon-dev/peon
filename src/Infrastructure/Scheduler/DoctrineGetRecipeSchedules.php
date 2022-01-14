<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Scheduler;

use Doctrine\DBAL\Connection;
use Peon\Domain\Scheduler\GetRecipeSchedules;

final class DoctrineGetRecipeSchedules implements GetRecipeSchedules
{
    public function __construct(private Connection $connection)
    {
    }



    public function get(): array
    {
        // TODO: find better way, how to make it in 1 SQL

        $sql = <<<SQL
SELECT project.project_id, recipe_name, MAX(scheduled_at) AS last_schedule
FROM project
CROSS JOIN LATERAL (SELECT json_array_elements(project.enabled_recipes)->>'recipe_name' AS recipe_name) enabled_recipe
LEFT JOIN job ON job.project_id = project.project_id AND job.enabled_recipe->>'recipe_name' = recipe_name
GROUP BY project.project_id, recipe_name;
SQL;


        $sql = <<<SQL
SELECT project.project_id, unnest(project.enabled_recipes) AS enabled_recipe_name
FROM project
SQL;

        /**
         * @var array<array{project_id: string, enabled_recipe_name: string}> $data
         */
        $data = $this->connection->executeQuery($sql)->fetchAllAssociative();
    }
}
