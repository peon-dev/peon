<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Memory;

use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;

final class InMemoryJobsCollection implements JobsCollection
{
    /**
     * @var array<string, Job>
     */
    private array $jobs = [];


    public function save(Job $job): void
    {
        $this->jobs[$job->jobId->id] = $job;
    }


    /**
     * @return array<string, Job>
     */
    public function findAll(): array
    {
        return $this->jobs;
    }


    /**
     * @throws JobNotFound
     */
    public function get(JobId $jobId): Job
    {
        return $this->jobs[$jobId->id] ?? throw new JobNotFound();
    }


    public function provideNextIdentity(): JobId
    {
        return new JobId((string) count($this->jobs));
    }
}
