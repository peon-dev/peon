<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\Exceptions\JobExecutionFailed;
use PHPMate\Domain\Job\Exceptions\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exceptions\JobHasNoCommands;
use PHPMate\Domain\Job\Exceptions\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exceptions\JobHasStartedAlready;
use PHPMate\Domain\Job\Exceptions\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\Exceptions\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RunTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private JobsCollection $jobs,
        private ProjectsCollection $projects,
        private Clock $clock,
        private CommandBus $commandBus,
    ) {}


    /**
     * @throws TaskNotFound
     * @throws ProjectNotFound
     * @throws \PHPMate\Domain\Job\Exceptions\JobHasNoCommands
     * @throws \PHPMate\Domain\Job\Exceptions\JobNotFound
     * @throws \PHPMate\Domain\Job\Exceptions\JobHasFinishedAlready
     * @throws JobHasStartedAlready
     * @throws JobHasNotStartedYet
     * @throws JobExecutionFailed
     */
    public function __invoke(RunTask $command): void
    {
        $task = $this->tasks->get($command->taskId);
        $project = $this->projects->get($task->projectId);

        $jobId = $this->jobs->nextIdentity();
        $job = new Job(
            $jobId,
            $project->projectId,
            $task->taskId,
            $task->name,
            $this->clock,
            $task->commands
        );

        $this->jobs->save($job);

        // TODO: should be event instead
        $this->commandBus->dispatch(
            new ExecuteJob($jobId)
        );
    }
}
