<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Job;

use Lcobucci\Clock\FrozenClock;
use Peon\Infrastructure\Job\DoctrineGetLongRunningJobs;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineGetLongRunningJobsTest extends KernelTestCase
{
    private DoctrineGetLongRunningJobs $getLongRunningJobs;
    private FrozenClock $clock;

    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->getLongRunningJobs = $container->get(DoctrineGetLongRunningJobs::class);
        $this->clock = $container->get(FrozenClock::class);
    }


    public function testItWorks(): void
    {
        $this->clock->setTo(
            (new \DateTimeImmutable(DataFixtures::JOB_4_DATETIME))->modify('+2 hours')
        );

        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $jobs = $this->getLongRunningJobs->olderThanHours(1);

        self::assertCount(1, $jobs);
        self::assertSame(DataFixtures::JOB_4_ID, $jobs[0]->jobId->id);
    }
}
