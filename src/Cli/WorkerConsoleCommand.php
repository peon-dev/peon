<?php

declare(strict_types=1);

namespace PHPMate\Cli;

use PHPMate\Domain\Job\JobExecutionFailed;
use PHPMate\Domain\Job\JobHasNotStarted;
use PHPMate\Domain\Job\JobHasStartedAlready;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Tools\Composer\ComposerCommandFailed;
use PHPMate\Domain\Tools\Git\GitCommandFailed;
use PHPMate\UseCase\ExecuteJobHandler;
use PHPMate\UseCase\ExecuteJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class WorkerConsoleCommand extends Command
{
    public function __construct(
        private MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->setName('worker:run');
    }


    /**
     * // TODO: these throws should be handled and not delegated
     * @throws JobHasNotStarted
     * @throws JobHasStartedAlready
     * @throws JobNotFound
     * @throws ProjectNotFound
     * @throws ComposerCommandFailed
     * @throws GitCommandFailed
     * @throws JobExecutionFailed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while(true) {
            $output->writeln('Next iteration');

            // Find job that should run
            $this->commandBus->dispatch(
                new ExecuteJob(
                    new JobId('')
                )
            );

            sleep(2);
        }
    }
}
