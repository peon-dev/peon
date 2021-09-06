<?php

declare(strict_types=1);

namespace PHPMate\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WorkerConsoleCommand extends Command
{
    public function __construct()
    {
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

            sleep(2);
        }
    }
}
