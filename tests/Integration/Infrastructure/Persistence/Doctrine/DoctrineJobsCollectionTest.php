<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Infrastructure\Persistence\Doctrine;

use Lcobucci\Clock\Clock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
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
        self::assertCount(0, $this->doctrineJobsCollection->all());

        $jobId = $this->doctrineJobsCollection->nextIdentity();

        // TODO: consider using some kind of factory
        $job = new Job(
            $jobId,
            new ProjectId(Uuid::uuid4()->toString()),
            new TaskId(Uuid::uuid4()->toString()),
            'Task name',
            $this->clock,
            ['command']
        );

        $this->doctrineJobsCollection->save($job);

        self::assertCount(1, $this->doctrineJobsCollection->all());

        $this->doctrineJobsCollection->get($jobId);
    }
}
