<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\ProjectDetail;

use Doctrine\DBAL\Connection;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use UXF\Hydrator\ObjectHydrator;

final class ProvideReadProjectDetail
{
    public function __construct(
        private Connection $connection,
        private ObjectHydrator $hydrator,
    ) {}

    /**
     * @throws ProjectNotFound
     */
    public function provide(ProjectId $projectId): ReadProjectDetail
    {
        $sql = <<<SQL
SELECT
       project_id AS "projectId",
       name,
       enabled_recipes AS "enabledRecipes",
       remote_git_repository_repository_uri AS "remoteGitRepositoryUri",
       build_configuration AS "buildConfiguration"
FROM project
WHERE project_id = :projectId
SQL;

        $resultSet = $this->connection->executeQuery($sql, ['projectId' => $projectId->id]);
        $row = $resultSet->fetchAssociative();

        if ($row === false) {
            throw new ProjectNotFound();
        }

        assert(is_string($row['enabledRecipes']));
        assert(is_string($row['buildConfiguration']));

        /**
         * @var array<EnabledRecipe> $enabledRecipes
         */
        $enabledRecipes = [];

        /**
         * @var array<array{recipe_name: string, baseline_hash: string|null}> $enabledRecipesJson
         */
        $enabledRecipesJson = Json::decode($row['enabledRecipes'], Json::FORCE_ARRAY);

        /**
         * @var array{skip_composer_install?: bool} $buildConfiguration
         */
        $buildConfiguration = Json::decode($row['buildConfiguration'], Json::FORCE_ARRAY);

        // TODO: i do not like this :-/ find better way
        $row['skipComposerInstall'] = $buildConfiguration['skip_composer_install'] ?? BuildConfiguration::DEFAULT_SKIP_COMPOSER_INSTALL_VALUE;

        // TODO: Temporary fix until we have proper hydrator
        foreach ($enabledRecipesJson as $enabledRecipe) {
            $enabledRecipes[] = EnabledRecipe::withoutConfiguration(
                RecipeName::from($enabledRecipe['recipe_name']),
                $enabledRecipe['baseline_hash'],
            );
        }

        $row['enabledRecipes'] = $enabledRecipes;

        return $this->hydrator->hydrateArray($row, ReadProjectDetail::class);
    }
}
