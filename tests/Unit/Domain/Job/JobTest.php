<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Job;

use Lcobucci\Clock\FrozenClock;
use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobHasNoCommands;
use PHPMate\Domain\Job\JobHasNotStarted;
use PHPMate\Domain\Job\JobHasStartedAlready;
use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Task\TaskId;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{
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
        $this->expectException(JobHasNotStarted::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->succeeds($clock);
    }


    public function testJobCanNotFailWithoutStarting(): void
    {
        $this->expectException(JobHasNotStarted::class);

        $clock = FrozenClock::fromUTC();
        $job = $this->createJob($clock);

        $job->fails($clock);
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
