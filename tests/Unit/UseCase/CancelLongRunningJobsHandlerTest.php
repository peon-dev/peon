<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Lcobucci\Clock\Clock;
use Lcobucci\Clock\FrozenClock;
use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Domain\Job\GetLongRunningJobs;
use Peon\Domain\Job\Job;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\CancelLongRunningJobs;
use Peon\UseCase\CancelLongRunningJobsHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancelLongRunningJobsHandlerTest extends TestCase
{
    public function testJobsWillBeCanceled(): void
    {
        $eventBus = $this->createMock(EventBus::class);
        $eventBus->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::isInstanceOf(JobStatusChanged::class));

        $clock = FrozenClock::fromUTC();

        $jobsCollection = $this->createMock(JobsCollection::class);

        $jobA = $this->createJobMockWithCancelExpected();
        $jobB = $this->createJobMockWithCancelExpected();

        $getLongRunningJobs = $this->createMock(GetLongRunningJobs::class);
        $getLongRunningJobs->expects(self::once())->method('olderThanHours')
            ->willReturn([$jobA, $jobB]);

        $handler = new CancelLongRunningJobsHandler(
            $getLongRunningJobs,
            $eventBus,
            $clock,
            $jobsCollection,
        );

        $handler->__invoke(new CancelLongRunningJobs());
    }


    /**
     * @return Job&MockObject
     */
    private function createJobMockWithCancelExpected(): MockObject
    {
        $job = $this->createTestProxy(Job::class, [
            new JobId(''),
            new ProjectId(''),
            'Title',
            null,
            FrozenClock::fromUTC(),
        ]);

        $job->expects(self::once())->method('cancel');

        return $job;
    }
}
