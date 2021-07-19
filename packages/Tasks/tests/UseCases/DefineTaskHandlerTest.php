<?php
declare(strict_types=1);

namespace PHPMate\Tasks\Tests\UseCases;

use PHPMate\Tasks\Tasks;
use PHPMate\Tasks\UseCases\DefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        // TODO: get + assert not exists

        $tasks = $this->createMock(Tasks::class);
        $tasks->expects(self::once())->method('provideNextIdentity');
        $tasks->expects(self::once())->method('save');

        $handler = new DefineTaskHandler();
        $handler->handle('Name', []);

        // TODO: get + assert exists
    }
}
