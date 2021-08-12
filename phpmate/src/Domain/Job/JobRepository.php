<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

interface JobRepository
{
    public function save(Job $job): void;


    /**
     * @return Job[]
     */
    public function findAll(): array;


    /**
     * @throws JobNotFound
     */
    public function get(int $id): Job;
}
