<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Cron\CronExpression;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;

final class ScheduleRecipesHandler
{
    public function __invoke(ScheduleRecipes $command): void
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
        $data = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();

        foreach ($data as $row) {
            try {
                // TODO: this is 1:N problem, carefully
                $sql = <<<SQL
SELECT project_id, recipe_name, MAX(scheduled_at) as last_schedule
FROM job
WHERE project_id = ? AND recipe_name = ?
GROUP BY project_id, recipe_name;
SQL;
                /**
                 * @var false|array{project_id: string, enabled_recipe_name: string, last_schedule: ?string} $projectData
                 */
                $projectData = $this->entityManager->getConnection()->executeQuery($sql, [
                    $row['project_id'],
                    $row['enabled_recipe_name']
                ])->fetchAssociative();

                if ($projectData === false || $projectData['last_schedule'] === null) {
                    $output->writeln('Scheduling recipe (for the first time) ' . $row['enabled_recipe_name'] . ' for project ' . $row['project_id']);

                    // should be scheduled, because never run before
                    $this->commandBus->dispatch(
                        new RunRecipe(
                            new ProjectId($row['project_id']),
                            RecipeName::from($row['enabled_recipe_name'])
                        )
                    );

                    continue;
                }

                // We need to compare times if it should run or not
                $lastSchedule = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $projectData['last_schedule']);
                assert($lastSchedule instanceof \DateTimeImmutable);

                // TODO: this is static for now to run every 8 hours but should not be!!
                $cron = new CronExpression('0 */8 * * *');
                $nextSchedule = $cron->getNextRunDate($lastSchedule);
                $now = $this->clock->now();

                if ($nextSchedule->format('Y-m-d H:i') <= $now->format('Y-m-d H:i')) {
                    $this->commandBus->dispatch(
                        new RunRecipe(
                            new ProjectId($row['project_id']),
                            RecipeName::from($row['enabled_recipe_name'])
                        )
                    );

                    $output->writeln('Scheduling recipe ' . $row['enabled_recipe_name'] . ' for project ' . $row['project_id']);
                }
            } catch (Throwable $throwable) {
                $this->logger->critical($throwable->getMessage(), [
                    'exception' => $throwable
                ]);
            }
        }
    }
}
