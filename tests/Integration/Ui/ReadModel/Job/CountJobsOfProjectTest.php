<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Job;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Job\CountJobsOfProject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CountJobsOfProjectTest extends KernelTestCase
{
    private CountJobsOfProject $countJobsOfProject;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->countJobsOfProject = $container->get(CountJobsOfProject::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $jobsCount = $this->countJobsOfProject->count(new ProjectId(DataFixtures::USER_1_PROJECT_1_ID));

        self::assertSame(5, $jobsCount);
    }
}
