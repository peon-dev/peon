<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Job;

use Lcobucci\Clock\FrozenClock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobHasFinishedAlready;
use PHPMate\Domain\Job\JobHasNoCommands;
use PHPMate\Domain\Job\JobHasNotStartedYet;
use PHPMate\Domain\Job\JobHasStartedAlready;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\TaskId;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{
    public function testJobCanBeScheduled(): void
    {
        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        self::assertNotNull($job->scheduledAt);
        self::assertNull($job->startedAt);
        self::assertNull($job->failedAt);
        self::assertNull($job->succeededAt);
    }


    public function testJobCanStart(): void
    {
        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);
        $job->start($clock);

        self::assertNotNull($job->scheduledAt);
        self::assertNotNull($job->startedAt);
        self::assertNull($job->failedAt);
        self::assertNull($job->succeededAt);
    }


    public function testJobCanSucceed(): void
    {
        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);
        $job->start($clock);
        $job->succeeds($clock);

        self::assertNotNull($job->scheduledAt);
        self::assertNotNull($job->startedAt);
        self::assertNull($job->failedAt);
        self::assertNotNull($job->succeededAt);
    }


    public function testJobCanFail(): void
    {
        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);
        $job->start($clock);
        $job->fails($clock);

        self::assertNotNull($job->scheduledAt);
        self::assertNotNull($job->startedAt);
        self::assertNotNull($job->failedAt);
        self::assertNull($job->succeededAt);
    }


    public function testJobMustContainCommands(): void
    {
        $this->expectException(JobHasNoCommands::class);

        new Job(
            new JobId(''),
            new ProjectId(''),
            new TaskId(''),
            '',
            FrozenClock::fromUTC(),
            []
        );
    }


    public function testJobCanNotBeStartedTwice(): void
    {
        $this->expectException(JobHasStartedAlready::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->start($clock);
        $job->start($clock);
    }


    public function testJobCanNotSuccessWithoutStarting(): void
    {
        $this->expectException(JobHasNotStartedYet::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->succeeds($clock);
    }


    public function testJobCanNotFailWithoutStarting(): void
    {
        $this->expectException(JobHasNotStartedYet::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->fails($clock);
    }


    public function testJobCanNotFailWhenAlreadyFailed(): void
    {
        $this->expectException(JobHasFinishedAlready::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->start($clock);
        $job->fails($clock);
        $job->fails($clock);
    }


    public function testJobCanNotFailWhenAlreadySucceeded(): void
    {
        $this->expectException(JobHasFinishedAlready::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->start($clock);
        $job->succeeds($clock);
        $job->fails($clock);
    }


    public function testJobCanNotSucceedWhenAlreadyFailed(): void
    {
        $this->expectException(JobHasFinishedAlready::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->start($clock);
        $job->fails($clock);
        $job->succeeds($clock);
    }


    public function testJobCanNotSucceedWhenAlreadySucceeded(): void
    {
        $this->expectException(JobHasFinishedAlready::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->start($clock);
        $job->succeeds($clock);
        $job->succeeds($clock);
    }


    public function testIndexingProcessesStartsFromOne(): void
    {
        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);
        $processResult = new ProcessResult('', 0, '', 0);

        $job->addProcessResult($processResult);
        $job->addProcessResult($processResult);

        self::assertSame(1, $job->processes[0]?->order);
        self::assertSame(2, $job->processes[1]?->order);
    }


    private function createJob(FrozenClock $clock): Job
    {
        return new Job(
            new JobId(''),
            new ProjectId(''),
            new TaskId(''),
            '',
            $clock,
            ['command']
        );
    }
}
