<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

use DateTimeImmutable;
use JetBrains\PhpStorm\Immutable;
use Lcobucci\Clock\Clock;
use Peon\Domain\Cookbook\Recipe;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
class Job
{
    public MergeRequest|null $mergeRequest = null;
    public DateTimeImmutable $scheduledAt;
    public DateTimeImmutable|null $canceledAt = null;
    public DateTimeImmutable|null $startedAt = null;
    public DateTimeImmutable|null $succeededAt = null;
    public DateTimeImmutable|null $failedAt = null;


    /**
     * @param array<string> $commands
     */
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProjectId $projectId,
        public readonly string $title,
        public readonly array|null $commands,
        Clock $clock,
        // TODO: make sure commands and recipe are not both null at the same time
        public readonly EnabledRecipe|null $enabledRecipe = null,
        public readonly TaskId|null $taskId = null,
    ) {
        $this->scheduledAt = $clock->now();
    }


    public static function scheduleRerun(Job $originalJob, JobId $newJobId, Clock $clock): self
    {
        return new self(
            $newJobId,
            $originalJob->projectId,
            $originalJob->title,
            $originalJob->commands,
            $clock,
            $originalJob->enabledRecipe,
            $originalJob->taskId,
        );
    }


    public static function scheduleFromRecipe(
        JobId $jobId,
        ProjectId $projectId,
        Clock $clock,
        string $title,
        EnabledRecipe $enabledRecipe,
    ): self
    {
        return new self(
            $jobId,
            $projectId,
            $title,
            null,
            $clock,
            $enabledRecipe
        );
    }


    public static function scheduleFromTask(
        JobId $jobId,
        ProjectId $projectId,
        Task $task,
        Clock $clock,
    ): self
    {
        return new self(
            $jobId,
            $projectId,
            $task->name,
            $task->commands,
            $clock,
            taskId: $task->taskId,
        );
    }


    /**
     * @throws JobHasStartedAlready
     */
    public function start(Clock $clock): void
    {
        $this->checkJobHasNotStarted();

        $this->startedAt = $clock->now();
    }


    public function cancel(Clock $clock): void
    {
        $this->canceledAt = $clock->now();
    }


    /**
     * @throws JobHasNotStartedYet
     * @throws JobHasFinishedAlready
     */
    public function succeeds(Clock $clock, MergeRequest|null $mergeRequest = null): void
    {
        $this->mergeRequest = $mergeRequest;

        $this->checkJobHasStarted();
        $this->checkJobHasNotFinished();

        $this->succeededAt = $clock->now();
    }


    /**
     * @throws JobHasNotStartedYet
     * @throws JobHasFinishedAlready
     */
    public function fails(Clock $clock, MergeRequest|null $mergeRequest = null): void
    {
        $this->mergeRequest = $mergeRequest;

        $this->checkJobHasStarted();
        $this->checkJobHasNotFinished();

        $this->failedAt = $clock->now();
    }


    /**
     * @throws JobHasFinishedAlready
     */
    private function checkJobHasNotFinished(): void
    {
        if ($this->succeededAt !== null) {
            throw new JobHasFinishedAlready();
        }

        if ($this->failedAt !== null) {
            throw new JobHasFinishedAlready();
        }
    }


    /**
     * @throws JobHasNotStartedYet
     */
    private function checkJobHasStarted(): void
    {
        if ($this->startedAt === null) {
            throw new JobHasNotStartedYet();
        }
    }


    /**
     * @throws JobHasStartedAlready
     */
    private function checkJobHasNotStarted(): void
    {
        if ($this->startedAt !== null) {
            throw new JobHasStartedAlready();
        }
    }
}
