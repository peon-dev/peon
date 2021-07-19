<?php
declare(strict_types=1);

namespace PHPMate\Tasks\Tests\UseCases;

use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\TaskNotFound;
use PHPMate\Tasks\Tasks;
use PHPMate\Tasks\UseCases\DefineTaskHandler;
use PHPMate\Tasks\UseCases\RedefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class RedefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeRedefined(): void
    {
        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())->method('get');
        $tasks->expects(self::once())->method('save');

        $handler = new RedefineTaskHandler($tasks);
        $handler->handle(new TaskId(''), 'Name', []);

        // TODO: get + assert is changed
    }


    public function testNonExistingTaskCanNotBeRedefined(): void
    {
        $this->expectException(TaskNotFound::class);

        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())->method('get')
            ->willThrowException(new TaskNotFound());

        $handler = new RedefineTaskHandler($tasks);
        $handler->handle(new TaskId(''), 'Name', []);
    }
}
