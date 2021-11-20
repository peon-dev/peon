<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\UseCase\RemoveTask;
use PHPMate\UseCase\RemoveTaskHandler;
use PHPUnit\Framework\TestCase;

final class RemoveTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        $tasksCollection = new InMemoryTasksCollection();
        $taskId = new TaskId('1');
        $tasksCollection->save(
            new Task($taskId, new ProjectId(''), 'Task', [])
        );

        self::assertCount(1, $tasksCollection->all());

        $handler = new RemoveTaskHandler($tasksCollection);
        $handler->__invoke(
            new RemoveTask($taskId)
        );

        self::assertCount(0, $tasksCollection->all());
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();

        $handler = new RemoveTaskHandler($tasksCollection);
        $handler->__invoke(
            new RemoveTask(new TaskId(''))
        );
    }
}
