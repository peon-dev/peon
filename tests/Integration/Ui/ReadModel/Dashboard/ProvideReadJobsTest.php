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

        $job = $readJobs[0];
        self::assertSame($job->jobId, DataFixtures::JOB_2_ID);
        self::assertNotNull($job->executionTime);

        $job = $readJobs[1];
        self::assertSame($job->jobId, DataFixtures::JOB_1_ID);
        self::assertNotNull($job->executionTime);
    }
}
