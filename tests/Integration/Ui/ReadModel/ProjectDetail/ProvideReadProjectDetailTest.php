<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Ui\ReadModel\ProjectDetail;

use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideReadProjectDetailTest extends KernelTestCase
{
    private ProvideReadProjectDetail $provideReadTasksRecipes;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadTasksRecipes = $container->get(ProvideReadProjectDetail::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \PHPMate\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::PROJECT_1_ID;

        $readProject = $this->provideReadTasksRecipes->provide(new ProjectId($projectId));

        self::assertSame(DataFixtures::PROJECT_NAME, $readProject->name);
        self::assertSame(DataFixtures::PROJECT_1_ID, $readProject->projectId);
        self::assertCount(2, $readProject->enabledRecipes);
    }
}
