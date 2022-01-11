<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Event\TaskAdded;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DefineTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private EventBus $eventBus,
    ) {}


    public function __invoke(DefineTask $command): void
    {
        // TODO: check project with id $command->projectId exists

        $taskId = $this->tasks->nextIdentity();

        $task = new Task(
            $taskId,
            $command->projectId,
            $command->name,
            $command->commands
        );

        $task->changeSchedule($command->schedule);

        $this->tasks->save($task);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new TaskAdded($taskId, $task->projectId)
        );
    }
}
