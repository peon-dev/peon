<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Memory;

use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobRepository;

final class InMemoryJobRepository implements JobRepository
{
    /**
     * @var Job[]
     */
    private array $jobs = [];


    public function save(Job $job): void
    {
        $this->jobs[$job->getTimestamp()] = $job;
    }


    /**
     * @return Job[]
     */
    public function findAll(): array
    {
        return $this->jobs;
    }


    public function get(int $id): Job
    {
        return $this->jobs[$id];
    }
}
