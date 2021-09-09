<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Events\JobCreated;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobHasNoCommands;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class RunTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private JobsCollection $jobs,
        private ProjectsCollection $projects,
        private Clock $clock,
        private MessageBusInterface $commandBus,
    ) {}


    /**
     * @throws TaskNotFound
     * @throws ProjectNotFound
     * @throws JobHasNoCommands
     */
    public function __invoke(RunTask $command): void
    {
        $task = $this->tasks->get($command->taskId);
        $project = $this->projects->get($task->projectId);

        $job = new Job(
            $this->jobs->nextIdentity(),
            $project->projectId,
            $task->taskId,
            $task->name,
            $this->clock,
            $task->commands
        );

        $this->jobs->save($job);

        // TODO: should be event instead
        $this->commandBus->dispatch(
            new ExecuteJob($job->jobId)
        );
    }
}
