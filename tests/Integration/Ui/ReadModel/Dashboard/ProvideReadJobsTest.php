<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Dashboard;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Dashboard\ProvideReadJobs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideReadJobsTest extends KernelTestCase
{
    private ProvideReadJobs $provideReadJobs;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadJobs = $container->get(ProvideReadJobs::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readJobs = $this->provideReadJobs->provide([
            new ProjectId(DataFixtures::USER_1_PROJECT_1_ID),
            new ProjectId(DataFixtures::USER_1_PROJECT_2_ID),
        ], 10);

        self::assertCount(5, $readJobs);

        $job = $readJobs[0];
        self::assertSame(DataFixtures::USER_1_JOB_5_ID, $job->jobId);
        self::assertNull($job->executionTime);
        self::assertNull($job->mergeRequestUrl);

        $job = $readJobs[1];
        self::assertSame(DataFixtures::USER_1_JOB_4_ID, $job->jobId);
        self::assertNull($job->executionTime);
        self::assertNull($job->mergeRequestUrl);

        $job = $readJobs[2];
        self::assertSame(DataFixtures::USER_1_JOB_3_ID, $job->jobId);
        self::assertNull($job->executionTime);
        self::assertNull($job->mergeRequestUrl);

        $job = $readJobs[3];
        self::assertSame(DataFixtures::USER_1_JOB_2_ID, $job->jobId);
        self::assertNotNull($job->executionTime);
        self::assertNotNull($job->mergeRequestUrl);

        $job = $readJobs[4];
        self::assertSame(DataFixtures::USER_1_JOB_1_ID, $job->jobId);
        self::assertNotNull($job->executionTime);
        self::assertNotNull($job->mergeRequestUrl);
    }


    public function testLimit(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readJobs = $this->provideReadJobs->provide([
            new ProjectId(DataFixtures::USER_1_PROJECT_1_ID)
        ], 1);

        self::assertCount(1, $readJobs);
    }
}
