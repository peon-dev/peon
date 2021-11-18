<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Infrastructure\Persistence\Doctrine;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\Task;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineTasksCollectionTest extends KernelTestCase
{
    private DoctrineTasksCollection $doctrineTasksCollection;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineTasksCollection = $container->get(DoctrineTasksCollection::class);
    }


    public function testPersistenceWorks(): void
    {
        self::assertCount(0, $this->doctrineTasksCollection->all());

        $taskId = $this->doctrineTasksCollection->nextIdentity();

        // TODO: consider using some kind of factory
        $task = new Task(
            $taskId,
            new ProjectId(Uuid::uuid4()->toString()),
            'Task name',
            ['command']
        );

        $this->doctrineTasksCollection->save($task);

        self::assertCount(1, $this->doctrineTasksCollection->all());

        $this->doctrineTasksCollection->get($taskId);
        $this->doctrineTasksCollection->remove($taskId);

        self::assertCount(0, $this->doctrineTasksCollection->all());
    }
}
