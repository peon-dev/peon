<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use DateTimeImmutable;
use JetBrains\PhpStorm\Immutable;
use Peon\Ui\ReadModel\JobStatus;

#[Immutable]
final class ReadJob
{
    public string $status = JobStatus::SCHEDULED;

    public function __construct(
        public string $jobId,
        public string $title,
        public string $projectId,
        public string $projectName,
        private float|null $executionTime,
        public string|null $taskId,
        public string|null $recipeName,
        public DateTimeImmutable $scheduledAt,
        public DateTimeImmutable|null $startedAt,
        public DateTimeImmutable|null $succeededAt,
        public DateTimeImmutable|null $failedAt,
        public string|null $mergeRequestUrl,
        public string|null $output = null,
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


    public function isRecipe(): bool
    {
        return $this->taskId === null;
    }


    public function getActionTime(): \DateTimeImmutable
    {
        return $this->failedAt
            ?? $this->succeededAt
            ?? $this->startedAt
            ?? $this->scheduledAt;
    }


    public function getExecutionTime()
    {
        return (int) $this->executionTime;
    }
}
