<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\InMemory;

use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use ReflectionObject;

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
    public function all(): array
    {
        return $this->jobs;
    }


    /**
     * @throws \PHPMate\Domain\Job\Exception\JobNotFound
     */
    public function get(JobId $jobId): Job
    {
        return $this->jobs[$jobId->id] ?? throw new JobNotFound();
    }


    public function nextIdentity(): JobId
    {
        return new JobId((string) count($this->jobs));
    }
}
