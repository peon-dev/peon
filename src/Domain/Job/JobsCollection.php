<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\Value\JobId;

interface JobsCollection
{
    public function nextIdentity(): JobId;


    public function save(Job $job): void;


    /**
     * @return array<Job>
     */
    public function all(): array;


    /**
     * @throws JobNotFound
     */
    public function get(JobId $jobId): Job;
}
