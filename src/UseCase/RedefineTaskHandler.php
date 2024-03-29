<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Task\Event\TaskChanged;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class RedefineTaskHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TasksCollection $tasks,
        private readonly EventBus $eventBus,
    ) {}


    /**
     * @throws TaskNotFound
     */
    public function __invoke(RedefineTask $command): void
    {
        $task = $this->tasks->get($command->taskId);

        // TODO: check project with id $task->projectId exists

        $task->changeDefinition($command->name, $command->commands, $command->mergeAutomatically);
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
