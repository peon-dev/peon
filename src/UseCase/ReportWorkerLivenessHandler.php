<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Worker\Exception\WorkerNotReportedAnythingYet;
use Peon\Domain\Worker\WorkerStatus;
use Peon\Domain\Worker\WorkerStatusesCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ReportWorkerLivenessHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Clock $clock,
        private readonly WorkerStatusesCollection $workerStatusesCollection,
    ) {
    }


    public function __invoke(ReportWorkerLiveness $command): void
    {
        try {
            $workerStatus = $this->workerStatusesCollection->get($command->workerId);
            $workerStatus->updateLiveness($this->clock);
        } catch (WorkerNotReportedAnythingYet) {
            $workerStatus = new WorkerStatus($command->workerId, $this->clock);
        }

        $this->workerStatusesCollection->save($workerStatus);
    }
}
