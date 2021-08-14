<?php
declare(strict_types=1);

namespace PHPMate\Tests\End2End;

use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\UseCase\DefineTaskHandler;
use PHPMate\UseCase\RedefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class RedefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRedefined(): void
    {
        $tasks = $this->createMock(TasksCollection::class);
        $tasks->expects(self::once())->method('get');

        $handler = new RedefineTaskHandler($tasks);
        $handler->handle(new TaskId(''), 'Name', []);

        // TODO: get + assert is changed
    }


    public function testNonExistingTaskCanNotBeRedefined(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasks = $this->createMock(TasksCollection::class);
        $tasks->expects(self::once())->method('get')
            ->willThrowException(new TaskNotFound());

        $handler = new RedefineTaskHandler($tasks);
        $handler->handle(new TaskId(''), 'Name', []);
    }
}