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


    public function testItWorks(): void
    {
        $readProjects = $this->provideReadProjects->provide();

        self::assertCount(0, $readProjects);
    }
}
