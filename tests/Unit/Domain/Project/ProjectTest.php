<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Project;

use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Domain\User\Value\UserId;
use Peon\Tests\DataFixtures\DataFixtures;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testProjectNameWillBeTakenFromRepository(): void
    {
        $project = $this->createProject();

        self::assertSame(DataFixtures::PROJECT_NAME, $project->name);
    }


    public function testEnableRecipe(): void
    {
        $project = $this->createProject();

        self::assertCount(0, $project->enabledRecipes);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $project->enableRecipe($recipeName);

        self::assertCount(1, $project->enabledRecipes);
        self::assertSame($recipeName, $project->enabledRecipes[0]->recipeName);
        self::assertNull($project->enabledRecipes[0]->baselineHash);

        $project->enableRecipe($recipeName, '12345');

        self::assertCount(1, $project->enabledRecipes);
        self::assertSame($recipeName, $project->enabledRecipes[0]->recipeName);
        self::assertSame('12345', $project->enabledRecipes[0]->baselineHash);
    }


    public function testDisableRecipe(): void
    {
        $project = $this->createProject();

        $project->enableRecipe(RecipeName::UNUSED_PRIVATE_METHODS);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES);
        self::assertCount(2, $project->enabledRecipes);

        $project->disableRecipe(RecipeName::TYPED_PROPERTIES);
        self::assertCount(1, $project->enabledRecipes);

        $project->disableRecipe(RecipeName::TYPED_PROPERTIES);
        self::assertCount(1, $project->enabledRecipes);

        $project->disableRecipe(RecipeName::UNUSED_PRIVATE_METHODS);
        self::assertCount(0, $project->enabledRecipes);
    }


    public function testConfigureRecipe(): void
    {
        $project = $this->createProject();

        // Lets enable some recipes first
        $project->enableRecipe(RecipeName::SWITCH_TO_MATCH);
        $project->enableRecipe(RecipeName::TYPED_PROPERTIES);

        // Fresh enabled recipe should contain default configuration
        self::assertEquals(RecipeJobConfiguration::createDefault(), $project->getEnabledRecipe(RecipeName::SWITCH_TO_MATCH)->configuration);

        $newConfiguration = new RecipeJobConfiguration(true);
        $project->configureRecipe(RecipeName::SWITCH_TO_MATCH, $newConfiguration);

        self::assertSame($newConfiguration, $project->getEnabledRecipe(RecipeName::SWITCH_TO_MATCH)->configuration);
    }


    public function testConfigureNotEnabledRecipeShouldThrowException(): void
    {
        $project = $this->createProject();

        $this->expectException(RecipeNotEnabled::class);

        $project->configureRecipe(RecipeName::SWITCH_TO_MATCH, RecipeJobConfiguration::createDefault());

    }


    private function createProject(): Project
    {
        return new Project(
            new ProjectId(''),
            DataFixtures::createRemoteGitRepository(),
            new UserId(''),
        );
    }
}
