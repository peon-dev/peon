<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class RerunJobHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly JobsCollection $jobsCollection,
        private readonly Clock $clock,
        private readonly CommandBus $commandBus,
        private readonly EventBus $eventBus,
        private readonly TasksCollection $tasksCollection,
    ) {}


    /**
     * @throws JobNotFound
     * @throws TaskNotFound
     * @throws JobExecutionFailed
     * @throws JobHasFinishedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasStartedAlready
     */
    public function __invoke(RerunJob $command): void
    {
        $originalJob = $this->jobsCollection->get($command->originalJobId);

        $newJob = Job::scheduleRerun(
            $originalJob,
            $command->newJobId,
            $this->clock,
        );

        $this->jobsCollection->save($newJob);

        $mergeAutomatically = $newJob->enabledRecipe?->configuration->mergeAutomatically ?? false;

        if ($newJob->taskId) {
            $task = $this->tasksCollection->get($newJob->taskId);
            $mergeAutomatically = $task->mergeAutomatically;
        }

        // TODO: should be event instead, because this is handled asynchronously
        $this->commandBus->dispatch(
            new ExecuteJob($command->newJobId, $mergeAutomatically)
        );

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new JobScheduled($command->newJobId, $originalJob->projectId)
        );
    }
}
