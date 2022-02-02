<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Process;

use Peon\Domain\Job\Value\JobId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Process\ProvideReadProcessesByJobId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProvideReadProcessesByJobIdTest extends KernelTestCase
{
    private ProvideReadProcessesByJobId $provideReadProcessesByJobId;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadProcessesByJobId = $container->get(ProvideReadProcessesByJobId::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readProcesses = $this->provideReadProcessesByJobId->provide(new JobId(DataFixtures::JOB_1_ID));

        self::assertCount(2, $readProcesses);

        foreach ($readProcesses as $readProcess) {
            self::assertSame(DataFixtures::JOB_1_ID, $readProcess->jobId);
            self::assertNotNull($readProcess->executionTime);
            self::assertNotNull($readProcess->output);
            self::assertNotNull($readProcess->exitCode);
        }
    }
}
