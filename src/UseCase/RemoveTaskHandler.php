<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Task\Event\TaskDeleted;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Event\EventBus;
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
        $task = $this->tasks->get($command->taskId);

        $this->tasks->remove($command->taskId);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new TaskDeleted($command->taskId, $task->projectId)
        );
    }
}
