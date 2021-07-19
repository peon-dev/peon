<?php
declare(strict_types=1);

namespace PHPMate\Tasks\Tests\UseCases;

use PHPMate\Tasks\TaskId;
use PHPMate\Tasks\Tasks;
use PHPMate\Tasks\UseCases\DefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        // TODO: get + assert not exists

        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())
            ->method('provideNextIdentity')
            ->willReturn(new TaskId(''));

        $tasks->expects(self::once())
            ->method('save');

        $handler = new DefineTaskHandler($tasks);
        $handler->handle('Name', ['']);

        // TODO: get + assert exists
    }
}
