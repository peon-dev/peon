<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Ui\ReadModel\Dashboard;

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


    /**
     * Data are populated from data fixtures
     * @see \PHPMate\Tests\DataFixtures\DataFixtures
     */
    public function testItWorks(): void
    {
        $readProjects = $this->provideReadProjects->provide();

        self::assertCount(1, $readProjects);

        $readProject = $readProjects[0];

        self::assertSame('phpmate-dogfood/rector', $readProject->name);
        self::assertSame(1, $readProject->tasksCount);
        self::assertSame(2, $readProject->jobsCount);
    }
}
