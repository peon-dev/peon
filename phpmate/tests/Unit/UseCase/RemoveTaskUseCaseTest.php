<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Infrastructure\Memory\InMemoryTasksCollection;
use PHPMate\UseCase\RemoveTask;
use PHPMate\UseCase\RemoveTaskUseCase;
use PHPUnit\Framework\TestCase;

final class RemoveTaskUseCaseTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        $tasksCollection = new InMemoryTasksCollection();
        $taskId = new TaskId('1');
        $tasksCollection->save(
            new Task($taskId, new ProjectId(''), 'Task', [])
        );

        self::assertCount(1, $tasksCollection->getAll());

        $handler = new RemoveTaskUseCase($tasksCollection);
        $handler->handle(
            new RemoveTask($taskId)
        );

        self::assertCount(0, $tasksCollection->getAll());
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();

        $handler = new RemoveTaskUseCase($tasksCollection);
        $handler->handle(
            new RemoveTask(new TaskId(''))
        );
    }
}
