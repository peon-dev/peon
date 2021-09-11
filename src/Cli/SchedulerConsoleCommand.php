<?php

declare(strict_types=1);

namespace PHPMate\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchedulerConsoleCommand extends Command
{
    public function __construct()
    {
        parent::__construct('phpmate:scheduler:run');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::SUCCESS;
    }
}
