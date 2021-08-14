<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use JetBrains\PhpStorm\Immutable;
use PHPMate\Domain\Project\ProjectId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
final class Job
{
    public string $status = JobStatus::SCHEDULED;

    public ?float $executionTime = null;

    private ?float $startTime = null;


    /**
     * @throws JobHasNoCommands
     */
    public function __construct(
        public JobId $jobId,
        public ProjectId $projectId,
        public string $taskName,
        public int $timestamp,
        public array $commands
    ) {
        $this->checkThereAreSomeCommands($commands);
    }


    /**
     * @throws JobHasStartedAlready
     */
    public function start(): void
    {
        $this->status = JobStatus::IN_PROGRESS;
        $this->startTime = microtime(true);
    }


    /**
     * @throws JobHasNotStarted
     */
    public function finish(): void
    {
        $this->executionTime = $this->calculateExecutionTime();
        $this->status = JobStatus::SUCCEEDED;
    }


    /**
     * @throws JobHasNotStarted
     */
    public function fail(): void
    {
        $this->executionTime = $this->calculateExecutionTime();
        $this->status = JobStatus::FAILED;
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


    /**
     * @param array<string> $commands
     * @throws JobHasNoCommands
     */
    private function checkThereAreSomeCommands(array $commands): void
    {
        if (count($commands) <= 0) {
            throw new JobHasNoCommands();
        }
    }


    /**
     * @throws JobHasNotStarted
     */
    private function calculateExecutionTime(): float
    {
        if ($this->startTime === null) {
            throw new JobHasNotStarted();
        }

        $finishTime = microtime(true);

        return $finishTime - $this->startTime;
    }
}
