<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class RunTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private JobsCollection $jobs,
        private ProjectsCollection $projects,
        private Clock $clock,
        private CommandBus $commandBus,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws TaskNotFound
     * @throws ProjectNotFound
     * @throws JobNotFound
     * @throws JobHasFinishedAlready
     * @throws JobHasStartedAlready
     * @throws JobHasNotStartedYet
     * @throws JobExecutionFailed
     */
    public function __invoke(RunTask $command): void
    {
        $task = $this->tasks->get($command->taskId);
        $project = $this->projects->get($task->projectId);

        $job = Job::scheduleFromTask(
            $command->jobId,
            $project->projectId,
            $task,
            $this->clock,
        );

        $this->jobs->save($job);

        // TODO: should be event instead, because this is handled asynchronously
        $this->commandBus->dispatch(
            new ExecuteJob($command->jobId)
        );

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new JobScheduled($command->jobId, $project->projectId)
        );
    }
}
