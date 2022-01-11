<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Event\TaskChanged;
use PHPMate\Domain\Task\Event\TaskDeleted;
use PHPMate\Domain\Task\Task;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use PHPMate\UseCase\RedefineTask;
use PHPMate\UseCase\RedefineTaskHandler;
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
            new Task($taskId, new ProjectId(''), 'Task', [])
        );

        $handler = new RedefineTaskHandler($tasksCollection, $eventBusSpy);
        $handler->__invoke(
            new RedefineTask(
                $taskId,
                'New name',
                [],
                null
            )
        );

        $task = $tasksCollection->get($taskId);

        self::assertSame('New name', $task->name);
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
                null
            )
        );
    }
}
