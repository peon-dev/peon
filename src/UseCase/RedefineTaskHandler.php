<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RedefineTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks
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
    }
}
