<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use PHPMate\Domain\Cookbook\Recipe;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideProjectReadRecipes
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
        private RecipesCollection $recipesCollection,
    ) {}


    /**
     * @return array<ReadRecipe>
     */
    public function provide(ProjectId $projectId): array
    {
        $sql = <<<SQL
SELECT
	project.recipe_name,
	job.job_id as last_job_id,
	job.started_at as last_job_started_at,
	job.failed_at as last_job_failed_at,
	job.succeeded_at as last_job_succeeded_at,
	job.scheduled_at as last_job_scheduled_at,
	job.merge_request_url as last_job_merge_request_url
FROM (
    SELECT project_id, unnest(enabled_recipes) as recipe_name
    FROM project
    WHERE project_id = :projectId
    ) project
LEFT JOIN job ON job.recipe_name = project.recipe_name AND job.scheduled_at = (
	SELECT MAX(latest_job.scheduled_at)
	FROM job latest_job
	WHERE
	    latest_job.recipe_name = project.recipe_name
	    AND latest_job.project_id = :projectId
	GROUP BY latest_job.recipe_name
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
            $recipes[$recipe->name->toString()] = $recipe;
        }

        // Add recipe title to every row
        array_walk($rows, static function(mixed &$row) use ($recipes) {
            $row['title'] = $recipes[$row['recipe_name']]->title;
        });

        return $this->hydrator->hydrateArrays($rows, ReadRecipe::class);
    }
}
