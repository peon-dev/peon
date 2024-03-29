<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Persistence\Doctrine;

use Lcobucci\Clock\Clock;
use Peon\Domain\Job\Job;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;
use Peon\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineJobsCollectionTest extends KernelTestCase
{
    private DoctrineJobsCollection $doctrineJobsCollection;

    private Clock $clock;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineJobsCollection = $container->get(DoctrineJobsCollection::class);
        $this->clock = $container->get(Clock::class);
    }


    public function testPersistenceWorks(): void
    {
        /*
         * We need to set baseline - number of rows already in database, before interacting with it
         * Because we do not have empty database - it is populated with fixtures data
         */
        $baselineCount = count($this->doctrineJobsCollection->all());

        $jobId = $this->doctrineJobsCollection->nextIdentity();
        // TODO: consider using some kind of factory
        $job = new Job(
            $jobId,
            new ProjectId(Uuid::uuid4()->toString()),
            'Task name',
            ['command'],
            $this->clock,
        );

        $this->doctrineJobsCollection->save($job);

        self::assertCount($baselineCount + 1, $this->doctrineJobsCollection->all());

        // Nothing to assert, just make sure record is in database and exception is not thrown
        $this->doctrineJobsCollection->get($jobId);
    }
}
