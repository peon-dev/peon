<?php

declare(strict_types=1);

namespace PHPMate\Cli;

use PHPMate\Domain\Job\JobId;
use PHPMate\UseCase\ExecuteJob;
use PHPMate\UseCase\ExecuteJobCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WorkerConsoleCommand extends Command
{
    public function __construct(
        private ExecuteJob $executeJobUseCase
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->setName('worker:run');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while(true) {
            $output->writeln('Next iteration');

            // Find job that should run
            $this->executeJobUseCase->handle(
                new ExecuteJobCommand(
                    new JobId('')
                )
            );

            sleep(2);
        }
    }
}
