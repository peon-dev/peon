<?php

declare(strict_types=1);

namespace PHPMate\Cli;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\RunTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchedulerConsoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Clock $clock,
        private CommandBus $commandBus
    ) {
        parent::__construct('phpmate:scheduler:run');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<SQL
SELECT task.task_id, task.schedule, MAX(scheduled_at) as last_schedule
FROM job
RIGHT JOIN task ON task.task_id = job.task_id
WHERE task.schedule IS NOT NULL
GROUP BY task.task_id;
SQL;

        $data = $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();

        foreach ($data as $row) {
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
            $nextSchedule = $cron->getNextRunDate($lastSchedule);

            if ($nextSchedule->format('Y-m-d H:i') <= $now->format('Y-m-d H:i')) {
                $this->commandBus->dispatch(
                    new RunTask($taskId)
                );

                $output->writeln('Scheduled task with id ' . $taskId->id);
            }
        }

        return self::SUCCESS;
    }
}
