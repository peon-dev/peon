<?php

declare(strict_types=1);

namespace Peon\Cli;

use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\CancelLongRunningJobs;
use Peon\UseCase\ScheduleRecipes;
use Peon\UseCase\ScheduleTasks;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Throwable;

#[AsCommand(name: 'peon:worker:run')]
final class WorkerConsoleCommand extends Command
{
    public function __construct(
        ConsumeMessagesCommand $consumeMessagesCommand,
    ) {
        parent::__construct();
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // Simulate long running process
        $output->writeln('meanwhile');
        sleep(3);
        $output->writeln('meanwhile after 3s');


        return self::SUCCESS;
    }
}
