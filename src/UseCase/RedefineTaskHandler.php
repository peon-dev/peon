<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Event\TaskChanged;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RedefineTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function __invoke(RedefineTask $command): void
    {
        $task = $this->tasks->get($command->taskId);

        // TODO: check project with id $task->projectId exists

        $task->changeDefinition($command->name, $command->commands);
        $task->changeSchedule($command->schedule);

        $this->tasks->save($task);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new TaskChanged(
                $task->taskId
            )
        );
    }
}
