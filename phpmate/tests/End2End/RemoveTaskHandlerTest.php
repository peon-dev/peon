<?php
declare(strict_types=1);

namespace PHPMate\Tests\End2End;

use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\UseCase\RemoveTaskHandler;
use PHPUnit\Framework\TestCase;

final class RemoveTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        // TODO: get + assert exists

        $tasks = $this->createMock(TasksCollection::class);
        $tasks->expects(self::once())->method('remove');

        $handler = new RemoveTaskHandler($tasks);
        $handler->handle(new TaskId(''));

        // TODO: get + assert not exists
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasks = $this->createMock(TasksCollection::class);
        $tasks->expects(self::once())->method('remove')
            ->willThrowException(new TaskNotFound());

        $handler = new RemoveTaskHandler($tasks);
        $handler->handle(new TaskId(''));
    }
}