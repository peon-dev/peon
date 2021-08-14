<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

interface JobsCollection
{
    public function provideNextIdentity(): JobId;


    public function save(Job $job): void;


    /**
     * @return array<Job>
     */
    public function findAll(): array;


    /**
     * @throws JobNotFound
     */
    public function get(JobId $jobId): Job;
}