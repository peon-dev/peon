<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Dashboard;

use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjects;
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
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readProjects = $this->provideReadProjects->provide();

        self::assertCount(2, $readProjects);

        $readProject = $readProjects[0];
        self::assertSame(DataFixtures::PROJECT_2_ID, $readProject->projectId);
        self::assertSame(0, $readProject->tasksCount);
        self::assertSame(0, $readProject->jobsCount);
        self::assertSame(0, $readProject->recipesCount);

        $readProject = $readProjects[1];
        self::assertSame(DataFixtures::PROJECT_1_ID, $readProject->projectId);
        self::assertSame(1, $readProject->tasksCount);
        self::assertSame(4, $readProject->jobsCount);
        self::assertSame(2, $readProject->recipesCount);
    }
}
