<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Job;

use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\Clock\Clock;
use Peon\Domain\Job\GetLongRunningJobs;
use Peon\Domain\Job\Job;

final class DoctrineGetLongRunningJobs implements GetLongRunningJobs
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Clock $clock,
    ) {}


    /**
     * @return array<Job>
     */
    public function olderThanHours(int $hours): array
    {
        $olderThan = $this->clock->now()
            ->modify(sprintf('-%d hours', $hours));

        assert($olderThan instanceof \DateTimeInterface);

        return $this->entityManager->createQueryBuilder()
            ->select('job')
            ->from(Job::class, 'job')
            ->where('job.startedAt IS NOT NULL')
            ->andWhere('job.canceledAt IS NULL')
            ->andWhere('job.succeededAt IS NULL')
            ->andWhere('job.failedAt IS NULL')
            ->andWhere('job.startedAt <= :olderThan')
            ->setParameter('olderThan', $olderThan->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}
