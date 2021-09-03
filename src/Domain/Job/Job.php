<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use JetBrains\PhpStorm\Immutable;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\TaskId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
final class Job
{
    public string $status = JobStatus::SCHEDULED;

    public ?float $duration = null;

    private ?float $startMicrotime = null;

    /**
     * @var array<JobProcess>
     */
    public array $processResults = [];

    public \DateTimeInterface $scheduledAt;


    /**
     * @param array<string> $commands
     * @throws JobHasNoCommands
     */
    public function __construct(
        public JobId $jobId,
        public ProjectId $projectId,
        public TaskId $taskId,
        public string $taskName,
        Clock $clock,
        public array $commands
    ) {
        $this->checkThereAreSomeCommands($commands);
        $this->scheduledAt = $clock->now();
    }


    /**
     * @throws JobHasStartedAlready
     */
    public function start(): void
    {
        if ($this->status !== JobStatus::SCHEDULED) {
            throw new JobHasStartedAlready();
        }

        $this->status = JobStatus::IN_PROGRESS;
        $this->startMicrotime = microtime(true);
    }


    /**
     * @throws JobHasNotStarted
     */
    public function finish(): void
    {
        $this->duration = $this->calculateDuration();
        $this->status = JobStatus::SUCCEEDED;
    }


    /**
     * @throws JobHasNotStarted
     */
    public function fail(): void
    {
        $this->duration = $this->calculateDuration();
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


    public function addProcessResult(ProcessResult $processResult): void
    {
        $this->processResults[] = new JobProcess(
            $this->jobId,
            count($this->processResults),
            $processResult
        );
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
    private function calculateDuration(): float
    {
        if ($this->startMicrotime === null) {
            throw new JobHasNotStarted();
        }

        $finishTime = microtime(true);

        return $finishTime - $this->startMicrotime;
    }
}
