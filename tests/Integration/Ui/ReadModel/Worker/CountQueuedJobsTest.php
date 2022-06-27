<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Worker;

use Peon\Ui\ReadModel\Worker\CountScheduledJobs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CountQueuedJobsTest extends KernelTestCase
{
    private CountScheduledJobs $countScheduledJobs;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->countScheduledJobs = $container->get(CountScheduledJobs::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */

        $scheduledJobsCount = $this->countScheduledJobs->count();

        self::assertSame(2, $scheduledJobsCount);
    }
}
