<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Memory;

use PHPMate\Worker\Domain\Job\Job;
use PHPMate\Worker\Domain\Job\JobRepository;

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
}
