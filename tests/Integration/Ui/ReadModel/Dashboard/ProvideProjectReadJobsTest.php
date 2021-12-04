<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Ui\ReadModel\Dashboard;

use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\Ui\ReadModel\Dashboard\ProvideProjectReadJobs;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadJobs;
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
         * @see \PHPMate\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::PROJECT_ID;

        $readJobs = $this->provideProjectReadJobs->provide(new ProjectId($projectId), 10);

        self::assertCount(3, $readJobs);

        $job = $readJobs[0];
        self::assertSame($job->jobId, DataFixtures::JOB_3_ID);
        self::assertNull($job->executionTime);

        $job = $readJobs[1];
        self::assertSame($job->jobId, DataFixtures::JOB_2_ID);
        self::assertNotNull($job->executionTime);

        $job = $readJobs[2];
        self::assertSame($job->jobId, DataFixtures::JOB_1_ID);
        self::assertNotNull($job->executionTime);
    }
}
