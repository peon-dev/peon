<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Task\Event\TaskAdded;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class DefineTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private EventBus $eventBus,
    ) {}


    public function __invoke(DefineTask $command): void
    {
        // TODO: check project with id $command->projectId exists

        $task = new Task(
            $command->taskId,
            $command->projectId,
            $command->name,
            $command->commands
        );

        $task->changeSchedule($command->schedule);

        $this->tasks->save($task);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new TaskAdded($command->taskId, $task->projectId)
        );
    }
}
