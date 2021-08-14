<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Infrastructure\Memory\InMemoryTasksCollection;
use PHPMate\UseCase\DefineTaskHandler;
use PHPUnit\Framework\TestCase;

final class DefineTaskHandlerTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        $tasksCollection = new InMemoryTasksCollection();

        self::assertCount(0, $tasksCollection->getAll());

        $handler = new DefineTaskHandler($tasksCollection);
        $handler->handle(new ProjectId(''), 'Name', ['']);

        self::assertCount(1, $tasksCollection->getAll());
    }
}
