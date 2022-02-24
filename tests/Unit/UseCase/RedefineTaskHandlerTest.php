<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Event\TaskChanged;
use Peon\Domain\Task\Event\TaskDeleted;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\RedefineTask;
use Peon\UseCase\RedefineTaskHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class RedefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRedefined(): void
    {
        $tasksCollection = new InMemoryTasksCollection();
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(TaskChanged::class));
        $taskId = new TaskId('1');
        $tasksCollection->save(
            new Task($taskId, new ProjectId(''), 'Task', [], true)
        );

        $handler = new RedefineTaskHandler($tasksCollection, $eventBusSpy);
        $handler->__invoke(
            new RedefineTask(
                $taskId,
                'New name',
                [],
                null,
                true
            )
        );

        $task = $tasksCollection->get($taskId);

        self::assertSame('New name', $task->name);
        self::assertTrue($task->mergeAutomatically);
    }


    public function testNonExistingTaskCanNotBeRedefined(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasksCollection = new InMemoryTasksCollection();
        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new RedefineTaskHandler($tasksCollection, $dummyEventBus);
        $handler->__invoke(
            new RedefineTask(
                new TaskId(''),
                'Name',
                [],
                null,
                false
            )
        );
    }
}
