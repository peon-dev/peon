<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Scheduler;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Domain\Scheduler\RecipeSchedule;

final class DoctrineGetRecipeSchedules implements GetRecipeSchedules
{
    public function __construct(
        private Connection $connection
    ) {}


    /**
     * @return array<RecipeSchedule>
     */
    public function get(): array
    {
        $sql = <<<SQL
SELECT project.project_id, recipe_name, MAX(scheduled_at) AS last_schedule
FROM project
CROSS JOIN LATERAL (SELECT json_array_elements(project.enabled_recipes)->>'recipe_name' AS recipe_name) enabled_recipe
LEFT JOIN job ON job.project_id = project.project_id AND job.enabled_recipe->>'recipe_name' = recipe_name
GROUP BY project.project_id, recipe_name;
SQL;

        $data = $this->connection->executeQuery($sql)->fetchAllAssociative();

        return array_map(static function(array $row) {
            /** @var array{project_id: string, recipe_name: string, last_schedule: string} $row */

            $lastTimeScheduledAt = $row['last_schedule'] ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_schedule']) : null;
            assert($lastTimeScheduledAt instanceof DateTimeImmutable || $lastTimeScheduledAt === null);

            return new RecipeSchedule(
                new ProjectId($row['project_id']),
                RecipeName::from($row['recipe_name']),
                $lastTimeScheduledAt,
            );
        }, $data);
    }
}
