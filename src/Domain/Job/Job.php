<?php

declare(strict_types=1);

namespace PHPMate\Domain\Job;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Immutable;
use Lcobucci\Clock\Clock;
use PHPMate\Domain\Process\JobProcess;
use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\TaskId;

#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
final class Job
{
    private \DateTimeInterface $scheduledAt;
    public ?\DateTimeInterface $startedAt = null;
    public ?\DateTimeInterface $succeededAt = null;
    public ?\DateTimeInterface $failedAt = null;

    /**
     * @var Collection<int, JobProcess>
     */
    public Collection $processes;

    /**
     * @param array<string> $commands
     * @throws JobHasNoCommands
     */
    public function __construct(
        public JobId $jobId,
        public ProjectId $projectId,
        private TaskId $taskId,
        public string $taskName,
        Clock $clock,
        public array $commands
    ) {
        $this->checkThereAreSomeCommands($commands);
        $this->scheduledAt = $clock->now();
        $this->processes = new ArrayCollection();
    }


    /**
     * @throws JobHasStartedAlready
     */
    public function start(Clock $clock): void
    {
        if ($this->startedAt !== null) {
            throw new JobHasStartedAlready();
        }

        $this->startedAt = $clock->now();
    }


    /**
     * @throws JobHasNotStarted
     */
    public function succeeds(Clock $clock): void
    {
        if ($this->startedAt === null) {
            throw new JobHasNotStarted();
        }

        $this->succeededAt = $clock->now();
    }


    /**
     * @throws JobHasNotStarted
     */
    public function fails(Clock $clock): void
    {
        if ($this->startedAt === null) {
            throw new JobHasNotStarted();
        }

        $this->failedAt = $clock->now();
    }


    public function addProcessResult(ProcessResult $processResult): void
    {
        $this->processes[] = new JobProcess(
            $this,
            count($this->processes),
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
}
