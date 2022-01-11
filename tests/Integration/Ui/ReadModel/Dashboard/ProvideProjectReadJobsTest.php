<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Dashboard;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Dashboard\ProvideProjectReadJobs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideProjectReadJobsTest extends KernelTestCase
{
    private ProvideProjectReadJobs $provideProjectReadJobs;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideProjectReadJobs = $container->get(ProvideProjectReadJobs::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::PROJECT_1_ID;

        $readJobs = $this->provideProjectReadJobs->provide(new ProjectId($projectId), 10);

        self::assertCount(3, $readJobs);

        $job = $readJobs[0];
        self::assertSame($job->jobId, DataFixtures::JOB_3_ID);
        self::assertNull($job->executionTime);
        self::assertNotNull($job->mergeRequestUrl);

        $job = $readJobs[1];
        self::assertSame($job->jobId, DataFixtures::JOB_2_ID);
        self::assertNotNull($job->executionTime);
        self::assertNotNull($job->mergeRequestUrl);

        $job = $readJobs[2];
        self::assertSame($job->jobId, DataFixtures::JOB_1_ID);
        self::assertNotNull($job->executionTime);
        self::assertNotNull($job->mergeRequestUrl);
    }
}
