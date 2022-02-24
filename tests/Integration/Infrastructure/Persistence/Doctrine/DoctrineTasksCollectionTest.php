<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Persistence\Doctrine;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Task;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineTasksCollection;
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
        /*
         * We need to set baseline - number of rows already in database, before interacting with it
         * Because we do not have empty database - it is populated with fixtures data
         */
        $baselineCount = count($this->doctrineTasksCollection->all());

        $taskId = $this->doctrineTasksCollection->nextIdentity();
        // TODO: consider using some kind of factory
        $task = new Task(
            $taskId,
            new ProjectId(Uuid::uuid4()->toString()),
            'Task name',
            ['command'],
            false,
        );

        $this->doctrineTasksCollection->save($task);
        self::assertCount($baselineCount + 1, $this->doctrineTasksCollection->all());

        // Nothing to assert, just make sure record is in database and exception is not thrown
        $this->doctrineTasksCollection->get($taskId);

        $this->doctrineTasksCollection->remove($taskId);
        self::assertCount($baselineCount, $this->doctrineTasksCollection->all());
    }
}
