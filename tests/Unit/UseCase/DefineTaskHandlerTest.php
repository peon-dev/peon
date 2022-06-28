<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Project\Event\ProjectDeleted;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Event\TaskAdded;
use Peon\Domain\Task\Value\TaskId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\DefineTask;
use Peon\UseCase\DefineTaskHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(TaskAdded::class));

        $tasksCollection = new InMemoryTasksCollection();

        self::assertCount(0, $tasksCollection->all());

        $handler = new DefineTaskHandler($tasksCollection, $eventBusSpy);
        $handler->__invoke(
            new DefineTask(
                new TaskId(''),
                new ProjectId(''),
                'Name',
                [''],
                null,
                false
            )
        );

        self::assertCount(1, $tasksCollection->all());
    }
}
