<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryTasksCollection;
use PHPMate\UseCase\DefineTaskCommand;
use PHPMate\UseCase\DefineTaskUseCase;
use PHPUnit\Framework\TestCase;

final class DefineTaskUseCaseTest extends TestCase
{
    public function testTaskCanBeDefined(): void
    {
        $tasksCollection = new InMemoryTasksCollection();

        self::assertCount(0, $tasksCollection->all());

        $handler = new DefineTaskUseCase($tasksCollection);
        $handler->__invoke(
            new DefineTaskCommand(
                new ProjectId(''),
                'Name',
                ['']
            )
        );

        self::assertCount(1, $tasksCollection->all());
    }
}
