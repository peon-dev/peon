<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Event\TaskDeleted;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RemoveTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function __invoke(RemoveTask $command): void
    {
        // TODO: check project with id $task->projectId exists

        $this->tasks->remove($command->taskId);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new TaskDeleted($command->taskId)
        );
    }
}
