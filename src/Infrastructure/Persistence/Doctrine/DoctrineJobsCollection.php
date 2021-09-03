<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use Ramsey\Uuid\Uuid;

final class DoctrineJobsCollection implements JobsCollection
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}


    /**
     * @throws JobNotFound
     */
    public function get(JobId $jobId): Job
    {
        $job = $this->entityManager->find(Job::class, $jobId);

        return $job ?? throw new JobNotFound();
    }


    public function save(Job $job): void
    {
        $this->entityManager->persist($job);
        $this->entityManager->flush();
    }


    /**
     * @return array<Job>
     */
    public function all(): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->getQuery()
            ->getResult();
    }


    public function nextIdentity(): JobId
    {
        return new JobId(Uuid::uuid4()->toString());
    }
}
