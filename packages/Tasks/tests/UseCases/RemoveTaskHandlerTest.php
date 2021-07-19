<?php
declare(strict_types=1);

namespace PHPMate\Tasks\Tests\UseCases;

use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\TaskNotFound;
use PHPMate\Tasks\Tasks;
use PHPMate\Tasks\UseCases\RemoveTaskHandler;
use PHPUnit\Framework\TestCase;

final class RemoveTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRemoved(): void
    {
        // TODO: get + assert exists

        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())->method('remove');

        $handler = new RemoveTaskHandler($tasks);
        $handler->handle(new TaskId(''));

        // TODO: get + assert not exists
    }


    public function testNonExistingTaskCanNotBeRemoved(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())->method('remove')
            ->willThrowException(new TaskNotFound());

        $handler = new RemoveTaskHandler($tasks);
        $handler->handle(new TaskId(''));
    }
}
