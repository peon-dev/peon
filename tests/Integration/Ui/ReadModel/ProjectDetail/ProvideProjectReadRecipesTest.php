<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\ProjectDetail;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProvideProjectReadRecipesTest extends KernelTestCase
{
    private ProvideProjectReadRecipes $provideProjectReadRecipes;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideProjectReadRecipes = $container->get(ProvideProjectReadRecipes::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::PROJECT_1_ID;

        $readRecipes = $this->provideProjectReadRecipes->provide(new ProjectId($projectId));

        self::assertCount(2, $readRecipes);

        $recipe = $readRecipes[0];
        self::assertSame(RecipeName::TYPED_PROPERTIES, $recipe->getRecipeName());
        self::assertNull($recipe->lastJobId);
        self::assertNull($recipe->lastJobMergeRequestUrl);

        $recipe = $readRecipes[1];
        self::assertSame(RecipeName::UNUSED_PRIVATE_METHODS, $recipe->getRecipeName());
        self::assertNotNull($recipe->lastJobId);
        self::assertNull($recipe->lastJobMergeRequestUrl);
    }
}
