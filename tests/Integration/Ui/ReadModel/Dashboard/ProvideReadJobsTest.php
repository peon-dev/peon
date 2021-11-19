<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Ui\ReadModel\Dashboard;

use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadJobs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideReadJobsTest extends KernelTestCase
{
    private ProvideReadJobs $provideReadJobs;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadJobs = $container->get(ProvideReadJobs::class);
    }


    /**
     * Data are populated from data fixtures
     * @see \PHPMate\Tests\DataFixtures\DataFixtures
     */
    public function testItWorks(): void
    {
        $readJobs = $this->provideReadJobs->provide(10);

        self::assertCount(2, $readJobs);

        $job1 = $readJobs[0];
        self::assertSame('phpmate-dogfood/rector', $job1->projectName);
        self::assertSame(DataFixtures::PROJECT_ID, $job1->projectId);
        self::assertSame($job1->jobId, DataFixtures::JOB_2_ID); // TODO: they should not be flipped
        self::assertSame('task', $job1->taskName);
        self::assertTrue($job1->hasFailed());
        // TODO: cover assertion for SUM(job_process.result_execution_time) as execution_time

        $job2 = $readJobs[1];
        self::assertSame('phpmate-dogfood/rector', $job2->projectName);
        self::assertSame(DataFixtures::PROJECT_ID, $job2->projectId);
        self::assertSame($job2->jobId, DataFixtures::JOB_1_ID); // TODO: they should not be flipped
        self::assertSame('task', $job2->taskName);
        self::assertTrue($job2->hasSucceeded());
        // TODO: cover assertion for SUM(job_process.result_execution_time) as execution_time
    }
}
