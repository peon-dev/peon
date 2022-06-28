<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\Worker\Exception\WorkerNotReportedAnythingYet;
use Peon\Domain\Worker\Value\WorkerId;
use Peon\Domain\Worker\WorkerStatus;
use Peon\Domain\Worker\WorkerStatusesCollection;

final class DoctrineWorkerStatusesCollection implements WorkerStatusesCollection
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}


    public function save(WorkerStatus $workerStatus): void
    {
        $this->entityManager->persist($workerStatus);
        $this->entityManager->flush();
    }


    /**
     * @throws WorkerNotReportedAnythingYet
     */
    public function get(WorkerId $workerId): WorkerStatus
    {
        $worker = $this->entityManager->find(WorkerStatus::class, $workerId);

        return $worker ?? throw new WorkerNotReportedAnythingYet();
    }
}
