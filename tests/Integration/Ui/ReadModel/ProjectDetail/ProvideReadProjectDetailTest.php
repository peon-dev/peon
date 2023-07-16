<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\ProjectDetail;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
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
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $readProject = $this->provideReadTasksRecipes->provide(new ProjectId($projectId));

        self::assertSame(DataFixtures::PROJECT_NAME, $readProject->name);
        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $readProject->projectId);
        self::assertCount(2, $readProject->enabledRecipes);
        self::assertFalse($readProject->skipComposerInstall);
        $configuration = $readProject->getRecipeConfiguration(RecipeName::TYPED_PROPERTIES);
        self::assertSame('ls -la', $configuration->afterScript);
    }
}
