<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\GetLongRunningJobs;
use Peon\Domain\Job\JobsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class CancelLongRunningJobsHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly GetLongRunningJobs $getLongRunningJobs,
        private readonly EventBus $eventBus,
        private readonly Clock $clock,
        private readonly JobsCollection $jobsCollection,
    ) {
    }


    public function __invoke(CancelLongRunningJobs $command): void
    {
        foreach ($this->getLongRunningJobs->olderThanHours(3) as $job) {
            $job->cancel($this->clock);

            // TODO: probably middleware would be better here, this causes to flush doctrine entity manager every time
            $this->jobsCollection->save($job);

            // TODO: this should be domain event and not being dispatched manually
            $this->eventBus->dispatch(new JobStatusChanged(
                $job->jobId,
                $job->projectId,
            ));
        }
    }
}
