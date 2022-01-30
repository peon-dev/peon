<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Dashboard;

use DateTimeImmutable;
use Peon\Ui\ReadModel\JobStatus;

final class ReadJob
{
    public readonly string $status;

    public readonly int|null $executionTime;

    public function __construct(
        public readonly string $jobId,
        public readonly string $title,
        public readonly string $projectId,
        public readonly string $projectName,
        public readonly string|null $taskId,
        public readonly string|null $recipeName,
        public readonly DateTimeImmutable $scheduledAt,
        public readonly DateTimeImmutable|null $startedAt,
        public readonly DateTimeImmutable|null $succeededAt,
        public readonly DateTimeImmutable|null $failedAt,
        public readonly string|null $mergeRequestUrl,
        float|null $executionTime,
        public readonly string|null $output = null,
    ){
        if ($failedAt !== null) {
            $status = JobStatus::FAILED;
        } elseif ($succeededAt !== null) {
            $status = JobStatus::SUCCEEDED;
        } elseif ($startedAt !== null) {
            $status = JobStatus::IN_PROGRESS;
        } else {
            $status = JobStatus::SCHEDULED;
        }

        $this->status = $status;
        $this->executionTime = $executionTime ? (int) $executionTime : null;
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
}
