<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
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
    ) {}


    /**
     * @throws JobNotFound
     */
    public function __invoke(RerunJob $command): void
    {
        $originalJob = $this->jobsCollection->get($command->jobId);
        $newJobId = $this->jobsCollection->nextIdentity();

        $newJob = Job::scheduleRerun(
            $originalJob,
            $newJobId,
            $this->clock,
        );

        $this->jobsCollection->save($newJob);

        $mergeAutomatically = $newJob->enabledRecipe?->configuration->mergeAutomatically ?? false;

        // TODO: should be event instead, because this is handled asynchronously
        $this->commandBus->dispatch(
            new ExecuteJob($newJobId, $mergeAutomatically)
        );

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new JobScheduled($newJobId, $originalJob->projectId)
        );
    }
}
