<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Event\TaskDeleted;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\RemoveTask;
use Peon\UseCase\RemoveTaskHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class RemoveTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        $tasksCollection = new InMemoryTasksCollection();
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(TaskDeleted::class));
        $taskId = new TaskId('1');
        $tasksCollection->save(
            new Task($taskId, new ProjectId(''), 'Task', [], false)
        );

        self::assertCount(1, $tasksCollection->all());

        $handler = new RemoveTaskHandler($tasksCollection, $eventBusSpy);
        $handler->__invoke(
            new RemoveTask($taskId)
        );

        self::assertCount(0, $tasksCollection->all());
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();
        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new RemoveTaskHandler($tasksCollection, $dummyEventBus);
        $handler->__invoke(
            new RemoveTask(new TaskId(''))
        );
    }
}
