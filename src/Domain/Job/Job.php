<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Immutable;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Cookbook\Recipe;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\GitProvider\Value\MergeRequest;
use PHPMate\Domain\Job\Exception\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exception\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exception\JobHasStartedAlready;
use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;

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
     * @var Collection<int, JobProcessResult>
     */
    public Collection $processes;

    public readonly ?TaskId $taskId;

    /**
     * @param array<string> $commands
     */
    public function __construct(
        public readonly JobId $jobId,
        public readonly ProjectId $projectId,
        public readonly string $title,
        public readonly array|null $commands,
        Clock $clock,
        public readonly RecipeName|null $recipeName = null,
    ) {
        $this->scheduledAt = $clock->now();
        $this->processes = new ArrayCollection();
    }


    public static function scheduleFromRecipe(
        JobId $jobId,
        ProjectId $projectId,
        Recipe $recipe,
        Clock $clock,
    ): self
    {
        return new self(
            $jobId,
            $projectId,
            $recipe->title,
            null,
            $clock,
            $recipe->name,
        );
    }


    public static function scheduleFromTask(
        JobId $jobId,
        ProjectId $projectId,
        Task $task,
        Clock $clock,
    ): self
    {
        $job = new self(
            $jobId,
            $projectId,
            $task->name,
            $task->commands,
            $clock,
        );

        $job->taskId = $task->taskId;

        return $job;
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


    public function addProcessResult(ProcessResult $processResult): void
    {
        $this->processes[] = new JobProcessResult(
            $this,
            1 + count($this->processes),
            $processResult
        );
    }

    /**
     * @TODO: delete
     */
    public function getMergeRequestUrl(): null|string
    {
        return $this->mergeRequest?->url;
    }


    /**
     * @TODO: delete
     */
    public function isPending(): bool
    {
        return $this->startedAt === null;
    }


    /**
     * @TODO: delete
     */
    public function isInProgress(): bool
    {
        return $this->startedAt !== null && $this->succeededAt === null && $this->failedAt === null;
    }


    /**
     * @TODO: delete
     */
    public function hasSucceeded(): bool
    {
        return $this->succeededAt !== null;
    }


    /**
     * @TODO: delete
     */
    public function hasFailed(): bool
    {
        return $this->failedAt !== null;
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
