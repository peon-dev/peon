<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;

final class RunTask
{
    public function __construct(
        private TasksCollection $tasks,
        private JobsCollection $jobs,
        private Clock $clock
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function handle(RunTaskCommand $command): void
    {
        $task = $this->tasks->get($command->taskId);

        $job = new Job(
            $this->jobs->nextIdentity(),
            $task->projectId,
            $task->taskId,
            $task->name,
            $this->clock,
            $task->commands
        );

        $this->jobs->save($job);
    }
}
