<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\Event\ProjectDeleted;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Task\Event\TaskAdded;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use PHPMate\UseCase\DefineTask;
use PHPMate\UseCase\DefineTaskHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(TaskAdded::class));

        $tasksCollection = new InMemoryTasksCollection();

        self::assertCount(0, $tasksCollection->all());

        $handler = new DefineTaskHandler($tasksCollection, $eventBusSpy);
        $handler->__invoke(
            new DefineTask(
                new ProjectId(''),
                'Name',
                [''],
                null
            )
        );

        self::assertCount(1, $tasksCollection->all());
    }
}
