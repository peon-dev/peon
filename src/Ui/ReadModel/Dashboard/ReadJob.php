<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\Dashboard;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Ui\ReadModel\JobStatus;

#[Immutable]
final class ReadJob
{
    public string $status = JobStatus::SCHEDULED;

    public function __construct(
        public string $jobId,
        public string $title,
        public string $projectId,
        public string $projectName,
        public ?float $executionTime,
        public ?\DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $succeededAt,
        public ?\DateTimeImmutable $failedAt,
    ){
        if ($failedAt !== null) {
            $this->status = JobStatus::FAILED;
        } elseif ($this->succeededAt !== null) {
            $this->status = JobStatus::SUCCEEDED;
        } elseif ($this->startedAt !== null) {
            $this->status = JobStatus::IN_PROGRESS;
        }
    }


    public function isPending(): bool
    {
        return $this->status === JobStatus::SCHEDULED;
    }


    public function isInProgress(): bool
    {
        return $this->status === JobStatus::IN_PROGRESS;
    }


    public function hasSucceeded(): bool
    {
        return $this->status === JobStatus::SUCCEEDED;
    }


    public function hasFailed(): bool
    {
        return $this->status === JobStatus::FAILED;
    }
}
