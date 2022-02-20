<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use Peon\Domain\Cookbook\Recipe;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideProjectReadRecipes
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
        private RecipesCollection $recipesCollection,
    ) {}


    /**
     * @return array<ReadRecipe>
     */
    public function provide(ProjectId $projectId): array
    {
        $sql = <<<SQL
SELECT
    project.recipe_name AS "recipeName",
    job.job_id AS "lastJobId",
    job.started_at AS "lastJobStartedAt",
    job.failed_at AS "lastJobFailedAt",
    job.succeeded_at AS "lastJobSucceededAt",
    job.scheduled_at AS "lastJobScheduledAt",
    job.canceled_at AS "lastJobCanceledAt",
    job.merge_request->>'url' AS "lastJobMergeRequestUrl"
FROM (
    SELECT project_id, json_array_elements(enabled_recipes)->>'recipe_name' as recipe_name
    FROM project
    WHERE project_id = :projectId
    ) project
LEFT JOIN job ON job.enabled_recipe->>'recipe_name' = project.recipe_name AND job.scheduled_at = (
    SELECT MAX(latest_job.scheduled_at)
    FROM job latest_job
    WHERE
        latest_job.enabled_recipe->>'recipe_name' = project.recipe_name
        AND latest_job.project_id = :projectId
    GROUP BY latest_job.enabled_recipe->>'recipe_name'
)
ORDER BY project.recipe_name
SQL;

        $resultSet = $this->connection->executeQuery($sql, ['projectId' => $projectId->id]);
        $rows = $resultSet->fetchAllAssociative();

        /**
         * @var array<string, Recipe> $recipes
         */
        $recipes = [];
        foreach ($this->recipesCollection->all() as $recipe) {
            $recipes[$recipe->name->value] = $recipe;
        }

        // Add recipe title to every row
        array_walk($rows, static function(mixed &$row) use ($recipes) {
            $row['title'] = $recipes[$row['recipeName']]->title;
        });

        return $this->hydrator->hydrateArrays($rows, ReadRecipe::class);
    }
}
