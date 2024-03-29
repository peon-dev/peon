<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
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
        // Temporary solution, https://github.com/phpstan/phpstan-doctrine/issues/221
        /** @var array<Job> $rows */
        $rows = $this->entityManager
            ->createQueryBuilder()
            ->select('j')
            ->from(Job::class, 'j')
            ->getQuery()
            ->getResult();

        return $rows;
    }


    public function nextIdentity(): JobId
    {
        return new JobId(Uuid::uuid4()->toString());
    }
}
