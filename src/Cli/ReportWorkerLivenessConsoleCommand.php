<?php

declare(strict_types=1);

namespace Peon\Cli;

use Peon\Domain\Worker\Value\WorkerId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\ReportWorkerLiveness;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'peon:worker:report-liveness')]
final class ReportWorkerLivenessConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commandBus->dispatch(
            new ReportWorkerLiveness(
                // TODO: should be something else than gethostname(), coz will not work on multiple workers on same host
                new WorkerId((string) gethostname()),
            ),
        );

        return self::SUCCESS;
    }
}
