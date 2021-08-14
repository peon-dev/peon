<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Infrastructure\Memory\InMemoryTasksCollection;
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

        self::assertCount(1, $tasksCollection->getAll());

        $handler = new RemoveTaskHandler($tasksCollection);
        $handler->handle($taskId);

        self::assertCount(0, $tasksCollection->getAll());
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();

        $handler = new RemoveTaskHandler($tasksCollection);
        $handler->handle(new TaskId(''));
    }
}
