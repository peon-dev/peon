<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RemoveTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private TasksCollection $tasks
    ) {}


    /**
     * @throws \PHPMate\Domain\Task\Exception\TaskNotFound
     */
    public function __invoke(RemoveTask $command): void
    {
        // TODO: check project with id $task->projectId exists

        $this->tasks->remove($command->taskId);
    }
}
