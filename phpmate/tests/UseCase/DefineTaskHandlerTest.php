<?php
declare(strict_types=1);

namespace PHPMate\Tests\UseCase;

use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\Tasks;
use PHPMate\UseCase\DefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        // TODO: get + assert not exists

        $tasks = $this->createMock(Tasks::class);
        $tasks
            ->expects(self::once())
            ->method('provideNextIdentity')
            ->willReturn(new TaskId(''));
        $tasks
            ->expects(self::once())
            ->method('add');

        $handler = new \PHPMate\UseCase\DefineTaskHandler($tasks);
        $handler->handle('Name', ['']);

        // TODO: get + assert exists
    }
}
