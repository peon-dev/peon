<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Value\EnabledRecipe;
use PHPMate\Domain\Project\Value\ProjectId;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ProvideReadProjectDetail
{
    public function __construct(
        private Connection $connection,
        private ArrayToValueObjectHydrator $hydrator,
    ) {}

    /**
     * @throws ProjectNotFound
     * @throws JsonException
     */
    public function provide(ProjectId $projectId): ReadProjectDetail
    {
        $sql = <<<SQL
SELECT project_id, name, enabled_recipes, remote_git_repository_repository_uri as remote_git_repository_uri
FROM project
WHERE project_id = :projectId
SQL;

        $resultSet = $this->connection->executeQuery($sql, ['projectId' => $projectId->id]);
        $row = $resultSet->fetchAssociative();

        if ($row === false) {
            throw new ProjectNotFound();
        }

        /**
         * @var array<EnabledRecipe> $enabledRecipes
         */
        $enabledRecipes = [];

        assert(is_string($row['enabled_recipes']));

        /**
         * @var array<array{recipe_name: string, baseline_hash: string|null}> $enabledRecipesJson
         */
        $enabledRecipesJson = Json::decode($row['enabled_recipes'], Json::FORCE_ARRAY);

        // TODO: Temporary fix until we have proper hydrator
        foreach ($enabledRecipesJson as $enabledRecipe) {
            $enabledRecipes[] = new EnabledRecipe(
                RecipeName::from($enabledRecipe['recipe_name']),
                $enabledRecipe['baseline_hash'],
            );
        }

        $row['enabled_recipes'] = $enabledRecipes;

        return $this->hydrator->hydrateArray($row, ReadProjectDetail::class);
    }
}
