<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Ui\ReadModel\Dashboard;

use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadProjects;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideReadProjectsTest extends KernelTestCase
{
    private ProvideReadProjects $provideReadProjects;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadProjects = $container->get(ProvideReadProjects::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \PHPMate\Tests\DataFixtures\DataFixtures
         */
        $readProjects = $this->provideReadProjects->provide();

        self::assertCount(1, $readProjects);

        $readProject = $readProjects[0];

        self::assertSame(DataFixtures::PROJECT_ID, $readProject->projectId);
        self::assertSame(1, $readProject->tasksCount);
        self::assertSame(2, $readProject->jobsCount);
    }
}
