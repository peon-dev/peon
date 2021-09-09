<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\UseCase\RemoveTaskCommand;
use PHPMate\UseCase\RemoveTask;
use PHPUnit\Framework\TestCase;

final class RemoveTaskTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        $tasksCollection = new InMemoryTasksCollection();
        $taskId = new TaskId('1');
        $tasksCollection->save(
            new Task($taskId, new ProjectId(''), 'Task', [])
        );

        self::assertCount(1, $tasksCollection->all());

        $handler = new RemoveTask($tasksCollection);
        $handler->__invoke(
            new RemoveTaskCommand($taskId)
        );

        self::assertCount(0, $tasksCollection->all());
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();

        $handler = new RemoveTask($tasksCollection);
        $handler->__invoke(
            new RemoveTaskCommand(new TaskId(''))
        );
    }
}
