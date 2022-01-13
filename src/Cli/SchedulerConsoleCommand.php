<?php

declare(strict_types=1);

namespace Peon\Cli;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\Clock\Clock;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RunRecipe;
use Peon\UseCase\RunTask;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchedulerConsoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Clock $clock,
        private CommandBus $commandBus,
        private LoggerInterface $logger
    ) {
        parent::__construct('peon:scheduler:run');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scheduleRecipes($output);
        $this->scheduleTasks($output);

        return self::SUCCESS;
    }


    private function scheduleTasks(OutputInterface $output): void
    {
        $sql = <<<SQL
SELECT task.task_id, task.schedule, MAX(scheduled_at) as last_schedule
FROM job
RIGHT JOIN task ON task.task_id = job.task_id
WHERE task.schedule IS NOT NULL
GROUP BY task.task_id;
SQL;

        /**
         * @var array<array{task_id: string, schedule: string, last_schedule: ?string}> $data
         */
        $data = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();

        foreach ($data as $row) {
            try {
                $cron = new CronExpression($row['schedule']);
                $now = $this->clock->now();
                $taskId = new TaskId($row['task_id']);

                // First time - never scheduled before
                if ($row['last_schedule'] === null) {
                    $this->commandBus->dispatch(
                        new RunTask($taskId)
                    );

                    $output->writeln('Scheduled task with id ' . $taskId->id);
                    continue;
                }

                $lastSchedule = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['last_schedule']);
                assert($lastSchedule instanceof \DateTimeImmutable);

                $nextSchedule = $cron->getNextRunDate($lastSchedule);

                if ($nextSchedule->format('Y-m-d H:i') <= $now->format('Y-m-d H:i')) {
                    $this->commandBus->dispatch(
                        new RunTask($taskId)
                    );

                    $output->writeln('Scheduled task with id ' . $taskId->id);
                }
            } catch (\Throwable $throwable) {
                $this->logger->critical($throwable->getMessage(), [
                    'exception' => $throwable
                ]);
            }
        }
    }


    private function scheduleRecipes(OutputInterface $output): void
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
            } catch (\Throwable $throwable) {
                $this->logger->critical($throwable->getMessage(), [
                    'exception' => $throwable
                ]);
            }
        }
    }
}
