<?php

declare(strict_types=1);

namespace Peon\Cli;

use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\ScheduleRecipes;
use Peon\UseCase\ScheduleTasks;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class SchedulerConsoleCommand extends Command
{
    public function __construct(
        private CommandBus $commandBus,
        private LoggerInterface $logger
    ) {
        parent::__construct('peon:scheduler:run');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->commandBus->dispatch(new ScheduleRecipes());
        } catch (Throwable $throwable) {
            $this->logger->critical($throwable->getMessage(), [
                'exception' => $throwable
            ]);
        }

        try {
            $this->commandBus->dispatch(new ScheduleTasks());
        } catch (Throwable $throwable) {
            $this->logger->critical($throwable->getMessage(), [
                'exception' => $throwable
            ]);
        }

        return self::SUCCESS;
    }
}
